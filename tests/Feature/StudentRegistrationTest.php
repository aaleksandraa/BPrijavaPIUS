<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Package;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Package::create([
            'name' => 'PIUS Plus',
            'slug' => 'pius-plus',
            'price' => 1000.00,
            'payment_type' => 'fixed',
            'description' => 'Test package for student registration',
            'is_active' => true,
            'show_on_landing' => true,
            'has_contract' => false,
        ]);
    }

    public function test_public_registration_allows_multiple_students_with_the_same_email(): void
    {
        $payload = [
            'first_name' => 'Ana',
            'last_name' => 'Test',
            'address' => 'Test 1',
            'postal_code' => '71000',
            'city' => 'Sarajevo',
            'country' => 'BiH',
            'phone' => '+38761111222',
            'email' => 'ana@example.com',
            'id_document_number' => 'ID-123',
            'entity_type' => 'individual',
            'payment_method' => 'full',
            'package_type' => 'pius-plus',
        ];

        $firstResponse = $this->postJson('/api/students', $payload);
        $secondResponse = $this->postJson('/api/students', [
            ...$payload,
            'first_name' => 'Anja',
            'id_document_number' => 'ID-456',
        ]);

        $firstResponse->assertStatus(201);
        $secondResponse->assertStatus(201);
        $this->assertDatabaseCount('students', 2);
        $this->assertSame(2, Student::where('email', 'ana@example.com')->count());
    }

    public function test_admin_can_update_student_to_email_that_another_student_already_uses(): void
    {
        $admin = AdminUser::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        Sanctum::actingAs($admin);

        $firstStudent = Student::create([
            'first_name' => 'Prvi',
            'last_name' => 'Student',
            'address' => 'Adresa 1',
            'postal_code' => '71000',
            'city' => 'Sarajevo',
            'country' => 'BiH',
            'phone' => '+38761111000',
            'email' => 'isti@example.com',
            'id_document_number' => 'ID-100',
            'entity_type' => 'individual',
            'payment_method' => 'full',
            'package_type' => 'pius-plus',
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        $secondStudent = Student::create([
            'first_name' => 'Drugi',
            'last_name' => 'Student',
            'address' => 'Adresa 2',
            'postal_code' => '71000',
            'city' => 'Sarajevo',
            'country' => 'BiH',
            'phone' => '+38761111001',
            'email' => 'drugi@example.com',
            'id_document_number' => 'ID-200',
            'entity_type' => 'individual',
            'payment_method' => 'full',
            'package_type' => 'pius-plus',
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        $response = $this->putJson("/api/students/{$secondStudent->id}", [
            'email' => $firstStudent->email,
        ]);

        $response->assertOk()
            ->assertJsonPath('email', 'isti@example.com');

        $this->assertSame(2, Student::where('email', 'isti@example.com')->count());
    }
}
