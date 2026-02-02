<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user and authenticate
        $admin = AdminUser::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        Sanctum::actingAs($admin);

        // Create a test package with a valid slug for full payment
        Package::create([
            'name' => 'PIUS Plus',
            'slug' => 'pius-plus',
            'price' => 1000.00,
            'payment_type' => 'fixed',
            'description' => 'Test package for invoice display tests',
            'is_active' => true,
            'show_on_landing' => true,
            'has_contract' => false,
        ]);
    }

    /**
     * Test invoice creation with mark_as_paid: true
     *
     * Requirements: 1.1, 2.1, 5.1, 5.2
     *
     * This test verifies:
     * - Invoice is created with status "paid"
     * - Invoice appears in the invoices list
     * - Student payment_status is updated correctly
     */
    public function test_invoice_creation_with_mark_as_paid_true(): void
    {
        // Create a student with full payment method
        $student = Student::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Test St',
            'postal_code' => '12345',
            'city' => 'Test City',
            'country' => 'Test Country',
            'phone' => '+1234567890',
            'email' => 'john.doe@test.com',
            'id_document_number' => 'ID123456',
            'entity_type' => 'individual',
            'payment_method' => 'full',
            'package_type' => 'pius-plus',
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        // Create invoice with mark_as_paid: true
        $invoiceData = [
            'student_id' => $student->id,
            'invoice_date' => now()->format('Y-m-d'),
            'payment_date' => now()->format('Y-m-d'),
            'description' => 'Test Invoice - Full Payment',
            'total_amount' => 1000.00,
            'vat_rate' => 20,
            'mark_as_paid' => true,
        ];

        $response = $this->postJson('/api/invoices', $invoiceData);

        // Assert invoice was created successfully
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'student_id',
            'invoice_number',
            'status',
            'total_amount',
        ]);

        // Verify invoice status is "paid"
        $invoice = Invoice::where('student_id', $student->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('paid', $invoice->status);

        // Verify invoice appears in the invoices list
        $invoicesResponse = $this->getJson('/api/invoices');
        $invoicesResponse->assertStatus(200);

        $invoices = $invoicesResponse->json();
        $this->assertNotEmpty($invoices);

        $createdInvoice = collect($invoices)->firstWhere('id', $invoice->id);
        $this->assertNotNull($createdInvoice, 'Invoice should appear in the invoices list');
        $this->assertEquals('paid', $createdInvoice['status']);

        // Verify student payment_status is updated
        $studentsResponse = $this->getJson('/api/students');
        $studentsResponse->assertStatus(200);

        $students = $studentsResponse->json();
        $updatedStudent = collect($students)->firstWhere('id', $student->id);

        $this->assertNotNull($updatedStudent);
        $this->assertEquals(1, $updatedStudent['paid_installments_count'],
            'Student with full payment should have paid_installments_count = 1');
        $this->assertEquals('1/1', $updatedStudent['payment_status'],
            'Student with full payment should have payment_status = "1/1"');
    }

    /**
     * Test invoice creation with mark_as_paid: false
     *
     * Requirements: 1.2, 5.1, 5.2
     *
     * This test verifies:
     * - Invoice is created with status "pending"
     * - Invoice appears in the invoices list
     * - Student payment_status is NOT updated (remains 0)
     */
    public function test_invoice_creation_with_mark_as_paid_false(): void
    {
        // Create a student with full payment method
        $student = Student::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'address' => '456 Test Ave',
            'postal_code' => '54321',
            'city' => 'Test Town',
            'country' => 'Test Country',
            'phone' => '+0987654321',
            'email' => 'jane.smith@test.com',
            'id_document_number' => 'ID654321',
            'entity_type' => 'individual',
            'payment_method' => 'full',
            'package_type' => 'pius-plus',
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        // Create invoice with mark_as_paid: false
        $invoiceData = [
            'student_id' => $student->id,
            'invoice_date' => now()->format('Y-m-d'),
            'description' => 'Test Invoice - Unpaid',
            'total_amount' => 1000.00,
            'vat_rate' => 20,
            'mark_as_paid' => false,
        ];

        $response = $this->postJson('/api/invoices', $invoiceData);

        // Assert invoice was created successfully
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'student_id',
            'invoice_number',
            'status',
            'total_amount',
        ]);

        // Verify invoice status is "pending"
        $invoice = Invoice::where('student_id', $student->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('pending', $invoice->status);

        // Verify invoice appears in the invoices list
        $invoicesResponse = $this->getJson('/api/invoices');
        $invoicesResponse->assertStatus(200);

        $invoices = $invoicesResponse->json();
        $this->assertNotEmpty($invoices);

        $createdInvoice = collect($invoices)->firstWhere('id', $invoice->id);
        $this->assertNotNull($createdInvoice, 'Invoice should appear in the invoices list');
        $this->assertEquals('pending', $createdInvoice['status']);

        // Verify student payment_status is NOT updated (should be 0/1)
        $studentsResponse = $this->getJson('/api/students');
        $studentsResponse->assertStatus(200);

        $students = $studentsResponse->json();
        $updatedStudent = collect($students)->firstWhere('id', $student->id);

        $this->assertNotNull($updatedStudent);
        $this->assertEquals(0, $updatedStudent['paid_installments_count'],
            'Student with unpaid invoice should have paid_installments_count = 0');
        $this->assertEquals('0/1', $updatedStudent['payment_status'],
            'Student with unpaid invoice should have payment_status = "0/1"');
    }

    /**
     * Test student with full payment method (paid)
     *
     * Requirements: 2.1, 4.1
     *
     * This test verifies:
     * - Student with payment_method: "full" and paid invoice
     * - Student displays "Plaćeno" (green badge)
     * - paid_installments_count = 1
     */
    public function test_student_with_full_payment_method_paid(): void
    {
        // Create a student with full payment method
        $student = Student::create([
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'address' => '789 Full Payment St',
            'postal_code' => '11111',
            'city' => 'Payment City',
            'country' => 'Test Country',
            'phone' => '+1111111111',
            'email' => 'alice.johnson@test.com',
            'id_document_number' => 'ID111111',
            'entity_type' => 'individual',
            'payment_method' => 'full',
            'package_type' => 'pius-plus',
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        // Create invoice with mark_as_paid: true
        $invoiceData = [
            'student_id' => $student->id,
            'invoice_date' => now()->format('Y-m-d'),
            'payment_date' => now()->format('Y-m-d'),
            'description' => 'Full Payment - Paid',
            'total_amount' => 1000.00,
            'vat_rate' => 20,
            'mark_as_paid' => true,
        ];

        $response = $this->postJson('/api/invoices', $invoiceData);
        $response->assertStatus(201);

        // Verify student payment status
        $studentsResponse = $this->getJson('/api/students');
        $studentsResponse->assertStatus(200);

        $students = $studentsResponse->json();
        $updatedStudent = collect($students)->firstWhere('id', $student->id);

        $this->assertNotNull($updatedStudent);
        $this->assertEquals('full', $updatedStudent['payment_method'],
            'Student should have payment_method = "full"');
        $this->assertEquals(1, $updatedStudent['paid_installments_count'],
            'Student with paid full payment should have paid_installments_count = 1');
        $this->assertEquals('1/1', $updatedStudent['payment_status'],
            'Student with paid full payment should have payment_status = "1/1"');

        // Verify that the frontend should display "Plaćeno" (green badge)
        // This is validated by paid_installments_count === 1
        $this->assertTrue($updatedStudent['paid_installments_count'] === 1,
            'Frontend should display "Plaćeno" with green badge when paid_installments_count === 1');
    }

    /**
     * Test student with full payment method (unpaid)
     *
     * Requirements: 2.1, 4.1
     *
     * This test verifies:
     * - Student with payment_method: "full" and unpaid invoice
     * - Student displays "Nije plaćeno" (red badge)
     * - paid_installments_count = 0
     */
    public function test_student_with_full_payment_method_unpaid(): void
    {
        // Create a student with full payment method
        $student = Student::create([
            'first_name' => 'Bob',
            'last_name' => 'Williams',
            'address' => '321 Unpaid St',
            'postal_code' => '22222',
            'city' => 'Unpaid City',
            'country' => 'Test Country',
            'phone' => '+2222222222',
            'email' => 'bob.williams@test.com',
            'id_document_number' => 'ID222222',
            'entity_type' => 'individual',
            'payment_method' => 'full',
            'package_type' => 'pius-plus',
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        // Create invoice with mark_as_paid: false
        $invoiceData = [
            'student_id' => $student->id,
            'invoice_date' => now()->format('Y-m-d'),
            'description' => 'Full Payment - Unpaid',
            'total_amount' => 1000.00,
            'vat_rate' => 20,
            'mark_as_paid' => false,
        ];

        $response = $this->postJson('/api/invoices', $invoiceData);
        $response->assertStatus(201);

        // Verify student payment status
        $studentsResponse = $this->getJson('/api/students');
        $studentsResponse->assertStatus(200);

        $students = $studentsResponse->json();
        $updatedStudent = collect($students)->firstWhere('id', $student->id);

        $this->assertNotNull($updatedStudent);
        $this->assertEquals('full', $updatedStudent['payment_method'],
            'Student should have payment_method = "full"');
        $this->assertEquals(0, $updatedStudent['paid_installments_count'],
            'Student with unpaid full payment should have paid_installments_count = 0');
        $this->assertEquals('0/1', $updatedStudent['payment_status'],
            'Student with unpaid full payment should have payment_status = "0/1"');

        // Verify that the frontend should display "Nije plaćeno" (red badge)
        // This is validated by paid_installments_count === 0
        $this->assertTrue($updatedStudent['paid_installments_count'] === 0,
            'Frontend should display "Nije plaćeno" with red badge when paid_installments_count === 0');
    }

    /**
     * Test student with installments (all paid)
     *
     * Requirements: 2.2, 4.2
     *
     * This test verifies:
     * - Student with payment_method: "installments" and all 3 installments paid
     * - Student displays "3/3" (green badge)
     * - paid_installments_count = 3
     */
    public function test_student_with_installments_all_paid(): void
    {
        // Create a student with installments payment method
        $student = Student::create([
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'address' => '111 Installment St',
            'postal_code' => '33333',
            'city' => 'Installment City',
            'country' => 'Test Country',
            'phone' => '+3333333333',
            'email' => 'charlie.brown@test.com',
            'id_document_number' => 'ID333333',
            'entity_type' => 'individual',
            'payment_method' => 'installments',
            'package_type' => 'pius-plus',
            'total_installments' => 3,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        // Create 3 invoices with installment_number: 1, 2, 3 and mark_as_paid: true
        for ($i = 1; $i <= 3; $i++) {
            $invoiceData = [
                'student_id' => $student->id,
                'invoice_date' => now()->format('Y-m-d'),
                'payment_date' => now()->format('Y-m-d'),
                'description' => "Installment {$i} of 3",
                'total_amount' => 333.33,
                'vat_rate' => 20,
                'installment_number' => $i,
                'mark_as_paid' => true,
            ];

            $response = $this->postJson('/api/invoices', $invoiceData);
            $response->assertStatus(201);
        }

        // Verify student payment status
        $studentsResponse = $this->getJson('/api/students');
        $studentsResponse->assertStatus(200);

        $students = $studentsResponse->json();
        $updatedStudent = collect($students)->firstWhere('id', $student->id);

        $this->assertNotNull($updatedStudent);
        $this->assertEquals('installments', $updatedStudent['payment_method'],
            'Student should have payment_method = "installments"');
        $this->assertEquals(3, $updatedStudent['paid_installments_count'],
            'Student with all 3 installments paid should have paid_installments_count = 3');
        $this->assertEquals(3, $updatedStudent['total_installments'],
            'Student should have total_installments = 3');
        $this->assertEquals('3/3', $updatedStudent['payment_status'],
            'Student with all installments paid should have payment_status = "3/3"');

        // Verify that the frontend should display "3/3" (green badge)
        // This is validated by paid_installments_count === total_installments
        $this->assertTrue(
            $updatedStudent['paid_installments_count'] === $updatedStudent['total_installments'],
            'Frontend should display "3/3" with green badge when all installments are paid'
        );
    }

    /**
     * Test student with installments (partially paid)
     *
     * Requirements: 2.3, 4.2
     *
     * This test verifies:
     * - Student with payment_method: "installments" and 1 of 3 installments paid
     * - Student displays "1/3" (yellow badge)
     * - paid_installments_count = 1
     */
    public function test_student_with_installments_partially_paid(): void
    {
        // Create a student with installments payment method
        $student = Student::create([
            'first_name' => 'Diana',
            'last_name' => 'Prince',
            'address' => '222 Partial St',
            'postal_code' => '44444',
            'city' => 'Partial City',
            'country' => 'Test Country',
            'phone' => '+4444444444',
            'email' => 'diana.prince@test.com',
            'id_document_number' => 'ID444444',
            'entity_type' => 'individual',
            'payment_method' => 'installments',
            'package_type' => 'pius-plus',
            'total_installments' => 3,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        // Create 1 invoice with installment_number: 1 and mark_as_paid: true
        $invoiceData = [
            'student_id' => $student->id,
            'invoice_date' => now()->format('Y-m-d'),
            'payment_date' => now()->format('Y-m-d'),
            'description' => 'Installment 1 of 3',
            'total_amount' => 333.33,
            'vat_rate' => 20,
            'installment_number' => 1,
            'mark_as_paid' => true,
        ];

        $response = $this->postJson('/api/invoices', $invoiceData);
        $response->assertStatus(201);

        // Verify student payment status
        $studentsResponse = $this->getJson('/api/students');
        $studentsResponse->assertStatus(200);

        $students = $studentsResponse->json();
        $updatedStudent = collect($students)->firstWhere('id', $student->id);

        $this->assertNotNull($updatedStudent);
        $this->assertEquals('installments', $updatedStudent['payment_method'],
            'Student should have payment_method = "installments"');
        $this->assertEquals(1, $updatedStudent['paid_installments_count'],
            'Student with 1 installment paid should have paid_installments_count = 1');
        $this->assertEquals(3, $updatedStudent['total_installments'],
            'Student should have total_installments = 3');
        $this->assertEquals('1/3', $updatedStudent['payment_status'],
            'Student with 1 installment paid should have payment_status = "1/3"');

        // Verify that the frontend should display "1/3" (yellow badge)
        // This is validated by 0 < paid_installments_count < total_installments
        $this->assertTrue(
            $updatedStudent['paid_installments_count'] > 0 &&
            $updatedStudent['paid_installments_count'] < $updatedStudent['total_installments'],
            'Frontend should display "1/3" with yellow badge when partially paid'
        );
    }

    /**
     * Test student with installments (none paid)
     *
     * Requirements: 2.4, 4.2
     *
     * This test verifies:
     * - Student with payment_method: "installments" and no installments paid
     * - Student displays "0/3" (red badge)
     * - paid_installments_count = 0
     */
    public function test_student_with_installments_none_paid(): void
    {
        // Create a student with installments payment method
        $student = Student::create([
            'first_name' => 'Edward',
            'last_name' => 'Norton',
            'address' => '333 Unpaid Installment St',
            'postal_code' => '55555',
            'city' => 'Unpaid City',
            'country' => 'Test Country',
            'phone' => '+5555555555',
            'email' => 'edward.norton@test.com',
            'id_document_number' => 'ID555555',
            'entity_type' => 'individual',
            'payment_method' => 'installments',
            'package_type' => 'pius-plus',
            'total_installments' => 3,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        // Create 1 invoice with mark_as_paid: false (or don't create any invoices)
        $invoiceData = [
            'student_id' => $student->id,
            'invoice_date' => now()->format('Y-m-d'),
            'description' => 'Installment 1 of 3 - Unpaid',
            'total_amount' => 333.33,
            'vat_rate' => 20,
            'installment_number' => 1,
            'mark_as_paid' => false,
        ];

        $response = $this->postJson('/api/invoices', $invoiceData);
        $response->assertStatus(201);

        // Verify student payment status
        $studentsResponse = $this->getJson('/api/students');
        $studentsResponse->assertStatus(200);

        $students = $studentsResponse->json();
        $updatedStudent = collect($students)->firstWhere('id', $student->id);

        $this->assertNotNull($updatedStudent);
        $this->assertEquals('installments', $updatedStudent['payment_method'],
            'Student should have payment_method = "installments"');
        $this->assertEquals(0, $updatedStudent['paid_installments_count'],
            'Student with no installments paid should have paid_installments_count = 0');
        $this->assertEquals(3, $updatedStudent['total_installments'],
            'Student should have total_installments = 3');
        $this->assertEquals('0/3', $updatedStudent['payment_status'],
            'Student with no installments paid should have payment_status = "0/3"');

        // Verify that the frontend should display "0/3" (red badge)
        // This is validated by paid_installments_count === 0
        $this->assertTrue(
            $updatedStudent['paid_installments_count'] === 0,
            'Frontend should display "0/3" with red badge when no installments are paid'
        );
    }
}
