<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MultiCourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(AdminUser::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('test')]));
    }

    private function enroll(string $slug, int $rates): Student
    {
        $package = Package::create([
            'name' => strtoupper($slug), 'slug' => $slug, 'price' => 900,
            'discount_price' => 600, 'payment_type' => $rates ? 'installments' : 'fixed',
            'is_active' => true, 'has_contract' => true,
        ]);
        for ($number = 1; $number <= $rates; $number++) {
            $package->installments()->create(['installment_number' => $number, 'amount' => 600 / $rates, 'due_description' => 'Rata '.$number]);
        }
        $response = $this->postJson('/api/students', [
            'first_name' => 'Ana', 'last_name' => 'Test', 'email' => 'ana@example.com',
            'phone' => '+38761111222', 'address' => 'Test 1', 'postal_code' => '71000',
            'city' => 'Sarajevo', 'country' => 'BiH', 'id_document_number' => 'ID-ANA',
            'entity_type' => 'individual', 'payment_method' => $rates ? 'installments' : 'full',
            'package_type' => $slug,
        ])->assertCreated();

        return Student::findOrFail($response->json('id'));
    }

    private function invoice(Student $student, ?int $number, bool $paid = true): array
    {
        return [
            'student_id' => $student->id, 'invoice_date' => '2026-10-29',
            'description' => 'Obuka', 'total_amount' => $number ? 600 / $student->total_installments : 600,
            'installment_number' => $number, 'mark_as_paid' => $paid,
        ];
    }

    public function test_same_person_has_separate_contracts_invoices_and_balances_for_three_courses(): void
    {
        $old = $this->enroll('pius-pro', 3);
        $custom = $this->enroll('eyeliner-lips', 2);
        $third = $this->enroll('lashes', 0);
        foreach ([$old, $custom] as $student) {
            Contract::create([
                'student_id' => $student->id, 'contract_type' => 'individual',
                'contract_number' => 'CONTRACT-'.$student->package_type,
                'contract_content' => 'Original '.$student->package_type, 'signed_at' => now(),
            ]);
        }
        $this->postJson('/api/invoices', $this->invoice($old, 1))->assertCreated();
        $this->postJson('/api/invoices', $this->invoice($custom, 1, false))->assertCreated();
        $this->postJson('/api/invoices', $this->invoice($third, null))->assertCreated();

        $students = collect($this->getJson('/api/students')->assertOk()->json())->keyBy('id');
        $this->assertSame('1/3', $students[$old->id]['payment_status']);
        $this->assertEquals(200, $students[$old->id]['paid_amount']);
        $this->assertEquals(400, $students[$old->id]['remaining_amount']);
        $this->assertSame('0/2', $students[$custom->id]['payment_status']);
        $this->assertEquals(600, $students[$custom->id]['remaining_amount']);
        $this->assertSame('1/1', $students[$third->id]['payment_status']);
        $this->assertEquals(0, $students[$third->id]['remaining_amount']);
        $this->assertDatabaseCount('students', 3);
        $this->assertDatabaseCount('contracts', 2);
        $this->assertDatabaseHas('contracts', ['student_id' => $old->id, 'contract_content' => 'Original pius-pro']);

        $invoices = $this->getJson('/api/invoices')->assertOk()->json();
        foreach ($invoices as $invoice) {
            $this->assertSame($students[$invoice['student_id']]['package_type'], $invoice['student']['package']['slug']);
        }
        $unpaid = collect($this->getJson('/api/invoices/students-unpaid')->assertOk()->json())->keyBy('id');
        $this->assertSame([2, 3], $unpaid[$old->id]['unpaid_installments']);
        $this->assertSame([1, 2], $unpaid[$custom->id]['unpaid_installments']);
        $this->assertSame('EYELINER-LIPS', $unpaid[$custom->id]['course_name']);
    }

    public function test_duplicate_and_invalid_rates_are_checked_within_the_selected_enrollment(): void
    {
        $old = $this->enroll('pius-pro', 3);
        $custom = $this->enroll('eyeliner-lips', 2);
        $this->postJson('/api/invoices', $this->invoice($old, 1))->assertCreated();
        $this->postJson('/api/invoices', $this->invoice($custom, 1, false))->assertCreated();
        $this->postJson('/api/invoices', $this->invoice($custom, 1))->assertUnprocessable()->assertJsonValidationErrors('installment_number');
        $this->postJson('/api/invoices', $this->invoice($custom, 3))->assertUnprocessable()->assertJsonValidationErrors('installment_number');
        $this->postJson('/api/invoices', $this->invoice($custom, 2))->assertCreated();
    }

    public function test_invoice_reversal_only_changes_its_own_course_and_keeps_payment_records(): void
    {
        $old = $this->enroll('pius-pro', 3);
        $custom = $this->enroll('eyeliner-lips', 2);
        $invoiceId = $this->postJson('/api/invoices', $this->invoice($old, 1))->assertCreated()->json('id');
        $this->postJson('/api/invoices', $this->invoice($custom, 1))->assertCreated();
        $this->putJson('/api/invoices/'.$invoiceId, ['status' => 'pending'])->assertOk();
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoiceId, 'status' => 'pending']);
        $summary = collect($this->getJson('/api/invoices/payment-status')->assertOk()->json())->keyBy('id');
        $this->assertSame('0/3', $summary[$old->id]['payment_status']);
        $this->assertSame('1/2', $summary[$custom->id]['payment_status']);
    }

    public function test_legacy_manual_payments_are_counted_once_and_course_history_cannot_be_reassigned(): void
    {
        $old = $this->enroll('pius-pro', 3);
        $custom = $this->enroll('eyeliner-lips', 2);
        $this->postJson('/api/invoices', $this->invoice($old, 1))->assertCreated();
        Payment::create(['student_id' => $old->id, 'installment_number' => 2, 'amount' => 200, 'status' => 'paid']);
        $students = collect($this->getJson('/api/students')->assertOk()->json())->keyBy('id');
        $this->assertEquals(400, $students[$old->id]['paid_amount']);
        $this->assertSame('2/3', $students[$old->id]['payment_status']);
        $this->assertEquals(0, $students[$custom->id]['paid_amount']);
        $this->putJson('/api/students/'.$old->id, ['package_type' => $custom->package_type])
            ->assertUnprocessable()->assertJsonValidationErrors('package_type');
        $this->assertSame('pius-pro', $old->fresh()->package_type);
        $package = Package::where('slug', 'pius-pro')->firstOrFail();
        $this->putJson('/api/packages/'.$package->id, ['slug' => 'changed-slug'])
            ->assertUnprocessable()->assertJsonValidationErrors('slug');
        $this->assertSame('pius-pro', $package->fresh()->slug);
    }

    public function test_invoice_pdf_and_email_identify_the_course(): void
    {
        $custom = $this->enroll('eyeliner-lips', 2);
        $id = $this->postJson('/api/invoices', $this->invoice($custom, 1))->assertCreated()->json('id');
        $invoice = Invoice::with('student.package')->findOrFail($id);
        $html = view('pdf.invoice', ['invoice' => $invoice, 'student' => $invoice->student, 'logoPath' => null])->render();
        $this->assertStringContainsString('Kurs: EYELINER-LIPS', $html);
        $email = view('emails.invoice-paid', ['invoice' => $invoice, 'student' => $invoice->student])->render();
        $this->assertStringContainsString('EYELINER-LIPS', $email);
    }

    public function test_full_payment_for_a_course_with_a_rate_plan_counts_as_one_payment(): void
    {
        $student = $this->enroll('eyeliner-lips', 2);
        $student->update(['payment_method' => 'full']);
        $this->postJson('/api/invoices', $this->invoice($student, null))->assertCreated();
        $entry = collect($this->getJson('/api/students')->assertOk()->json())->firstWhere('id', $student->id);
        $this->assertSame('1/1', $entry['payment_status']);
        $this->assertEquals(600, $entry['paid_amount']);
        $this->assertEquals(0, $entry['remaining_amount']);
    }

    public function test_manual_service_invoice_does_not_pay_course_or_reserve_a_rate(): void
    {
        $student = $this->enroll('eyeliner-lips', 2);
        $payload = array_merge($this->invoice($student, null), [
            'invoice_type' => 'other', 'description' => 'Edukativni materijal', 'total_amount' => 75,
        ]);
        $id = $this->postJson('/api/invoices', $payload)->assertCreated()
            ->assertJsonPath('installment_number', null)->assertJsonPath('invoice_type', 'other')->json('id');
        $entry = $this->getJson('/api/students/'.$student->id)->assertOk();
        $entry->assertJsonPath('payment_status', '0/2')->assertJsonPath('paid_amount', 0)
            ->assertJsonPath('remaining_amount', 600);
        $this->postJson('/api/invoices', array_merge($payload, ['installment_number' => 1]))
            ->assertUnprocessable()->assertJsonValidationErrors('installment_number');

        $invoice = Invoice::with('student.package')->findOrFail($id);
        $html = view('pdf.invoice', ['invoice' => $invoice, 'student' => $invoice->student, 'logoPath' => null])->render();
        $this->assertStringContainsString('Edukativni materijal', $html);
        $this->assertStringNotContainsString('Kurs: EYELINER-LIPS', $html);
        $email = view('emails.invoice-paid', ['invoice' => $invoice, 'student' => $invoice->student])->render();
        $this->assertStringNotContainsString('za kurs', $email);

        $this->deleteJson('/api/invoices/'.$id)->assertNoContent();
        $this->assertDatabaseMissing('payments', ['invoice_id' => $id]);
        $this->assertEquals(0, $student->fresh()->paymentSummary()['paid_amount']);
        $this->postJson('/api/invoices', $this->invoice($student, 1))->assertCreated();
    }

    public function test_marking_a_manual_invoice_paid_later_does_not_mark_full_course_paid(): void
    {
        $student = $this->enroll('lashes', 0);
        $payload = array_merge($this->invoice($student, null, false), ['invoice_type' => 'other']);
        $id = $this->postJson('/api/invoices', $payload)->assertCreated()->json('id');
        $this->putJson('/api/invoices/'.$id, ['status' => 'paid'])->assertOk();
        $entry = $this->getJson('/api/students/'.$student->id)->assertOk();
        $entry->assertJsonPath('payment_status', '0/1')->assertJsonPath('paid_amount', 0);
        $this->assertDatabaseHas('payments', ['invoice_id' => $id, 'status' => 'paid']);
        $this->putJson('/api/invoices/'.$id, ['status' => 'cancelled'])->assertOk();
        $this->assertDatabaseHas('payments', ['invoice_id' => $id, 'status' => 'pending']);
    }

    public function test_admin_adds_an_offered_course_using_saved_personal_details(): void
    {
        $original = $this->enroll('pius-pro', 3);
        $this->enroll('lashes', 0);
        $original->update(['status' => 'completed', 'company_name' => 'Saved company']);
        $this->postJson('/api/invoices', $this->invoice($original, 1))->assertCreated();

        $response = $this->postJson('/api/students/'.$original->id.'/enrollments', [
            'package_type' => 'lashes', 'payment_method' => 'full', 'first_name' => 'Changed',
        ])->assertCreated()->assertJsonPath('first_name', 'Ana')->assertJsonPath('email', $original->email)
            ->assertJsonPath('company_name', 'Saved company')->assertJsonPath('status', 'enrolled')
            ->assertJsonPath('package.slug', 'lashes');
        $this->assertNotEquals($original->id, $response->json('id'));
        $this->assertSame('pius-pro', $original->fresh()->package_type);
        $this->assertSame('completed', $original->fresh()->status);
        $this->assertEquals(200, $original->fresh()->paymentSummary()['paid_amount']);
        $new = Student::findOrFail($response->json('id'));
        $this->assertEquals(0, $new->paymentSummary()['paid_amount']);
        $this->assertCount(0, $new->invoices);

        $this->postJson('/api/students/'.$original->id.'/enrollments', ['package_type' => 'unknown', 'payment_method' => 'full'])
            ->assertUnprocessable()->assertJsonValidationErrors('package_type');
        $this->postJson('/api/students/'.$original->id.'/enrollments', ['package_type' => 'lashes', 'payment_method' => 'installments'])
            ->assertUnprocessable()->assertJsonValidationErrors('payment_method');
        Package::where('slug', 'lashes')->update(['is_active' => false]);
        $this->postJson('/api/students/'.$original->id.'/enrollments', ['package_type' => 'lashes', 'payment_method' => 'full'])
            ->assertUnprocessable()->assertJsonValidationErrors('package_type');
        $this->assertDatabaseCount('students', 3);
    }
}
