<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Package;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContractDiscountDisplayTest extends TestCase
{
    public static function prices(): array
    {
        return [
            'discount from course settings' => [999.99, '999,99€'],
            'another discount amount' => [99.99, '99,99€'],
            'zero discount price' => [0, '0,00€'],
            'no discount' => [null, null],
        ];
    }

    #[DataProvider('prices')]
    public function test_pdf_and_admin_email_show_the_configured_discount(?float $discount, ?string $formatted): void
    {
        $data = $this->documentData($discount);
        foreach (['pdf.contract', 'emails.contract-signed-admin'] as $view) {
            $html = view($view, $data)->render();
            $this->assertStringContainsString('EYELINER LIPS', $html);
            $this->assertStringContainsString('1.800,00€', $html);
            if ($formatted !== null) {
                $this->assertMatchesRegularExpression('/<span style="text-decoration: line-through;[^\"]*">1\.800,00€<\/span>/', $html);
                $this->assertMatchesRegularExpression('/<strong[^>]*>'.preg_quote($formatted, '/').'<\/strong>/', $html);
            } else {
                $this->assertStringNotContainsString('line-through', $html);
                $this->assertStringNotContainsString('999,99€', $html);
            }
        }
        // Display fixes must not rewrite the saved contract or package price.
        $this->assertSame('Originalni potpisani tekst.', $data['contract']->contract_content);
        $this->assertSame('1800.00', $data['package']->price);
    }

    public function test_discounted_contract_generates_a_pdf(): void
    {
        $pdf = Pdf::loadView('pdf.contract', $this->documentData(999.99))->output();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(1000, strlen($pdf));
    }

    private function documentData(?float $discount): array
    {
        return [
            'student' => new Student([
                'first_name' => 'Ana', 'last_name' => 'Test', 'email' => 'ana@example.com',
                'phone' => '12345', 'address' => 'Test 1', 'postal_code' => '71000',
                'city' => 'Sarajevo', 'country' => 'BiH', 'id_document_number' => 'ID-TEST',
                'package_type' => 'eyeliner-lips', 'entity_type' => 'individual', 'payment_method' => 'installments',
            ]),
            'package' => new Package(['name' => 'EYELINER LIPS', 'price' => 1800, 'discount_price' => $discount]),
            'contract' => new Contract([
                'contract_number' => 'PIUS-TEST', 'contract_content' => 'Originalni potpisani tekst.', 'signed_at' => now(),
            ]),
        ];
    }
}
