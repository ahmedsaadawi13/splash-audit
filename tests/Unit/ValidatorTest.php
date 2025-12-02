<?php
/**
 * Validator Tests
 * Tests input validation functionality
 */

require_once __DIR__ . '/../TestCase.php';

class ValidatorTest extends TestCase {

    /**
     * Test required field validation
     */
    public function testRequiredValidation() {
        $validator = new Validator(['name' => '']);
        $validator->required('name');

        $this->assertTrue($validator->fails());

        $validator2 = new Validator(['name' => 'John']);
        $validator2->required('name');

        $this->assertFalse($validator2->fails());
    }

    /**
     * Test email validation
     */
    public function testEmailValidation() {
        $invalidEmails = [
            'notanemail',
            'missing@domain',
            '@nodomain.com',
            'spaces in@email.com'
        ];

        foreach ($invalidEmails as $email) {
            $validator = new Validator(['email' => $email]);
            $validator->email('email');
            $this->assertTrue($validator->fails(), "Failed to invalidate: $email");
        }

        $validEmails = [
            'user@example.com',
            'test.user@domain.co.uk',
            'admin+tag@site.org'
        ];

        foreach ($validEmails as $email) {
            $validator = new Validator(['email' => $email]);
            $validator->email('email');
            $this->assertFalse($validator->fails(), "Failed to validate: $email");
        }
    }

    /**
     * Test integer validation
     */
    public function testIntegerValidation() {
        $validator = new Validator(['age' => 'not a number']);
        $validator->integer('age');
        $this->assertTrue($validator->fails());

        $validator2 = new Validator(['age' => 25]);
        $validator2->integer('age');
        $this->assertFalse($validator2->fails());

        $validator3 = new Validator(['age' => '30']);
        $validator3->integer('age');
        $this->assertFalse($validator3->fails());
    }

    /**
     * Test minimum length validation
     */
    public function testMinLengthValidation() {
        $validator = new Validator(['password' => '123']);
        $validator->minLength('password', 8);
        $this->assertTrue($validator->fails());

        $validator2 = new Validator(['password' => '12345678']);
        $validator2->minLength('password', 8);
        $this->assertFalse($validator2->fails());
    }

    /**
     * Test maximum length validation
     */
    public function testMaxLengthValidation() {
        $longString = str_repeat('a', 256);
        $validator = new Validator(['title' => $longString]);
        $validator->maxLength('title', 255);
        $this->assertTrue($validator->fails());

        $validator2 = new Validator(['title' => 'Short title']);
        $validator2->maxLength('title', 255);
        $this->assertFalse($validator2->fails());
    }

    /**
     * Test pattern validation
     */
    public function testPatternValidation() {
        // Test phone number pattern
        $validator = new Validator(['phone' => '123-456-7890']);
        $validator->pattern('phone', '/^\d{3}-\d{3}-\d{4}$/');
        $this->assertFalse($validator->fails());

        $validator2 = new Validator(['phone' => 'invalid']);
        $validator2->pattern('phone', '/^\d{3}-\d{3}-\d{4}$/');
        $this->assertTrue($validator2->fails());
    }

    /**
     * Test URL validation
     */
    public function testUrlValidation() {
        $validUrls = [
            'http://example.com',
            'https://www.example.com',
            'https://example.com/path?query=value'
        ];

        foreach ($validUrls as $url) {
            $validator = new Validator(['website' => $url]);
            $validator->url('website');
            $this->assertFalse($validator->fails(), "Failed to validate URL: $url");
        }

        $invalidUrls = [
            'not a url',
            'htp://wrong-protocol.com',
            'missing-protocol.com'
        ];

        foreach ($invalidUrls as $url) {
            $validator = new Validator(['website' => $url]);
            $validator->url('website');
            $this->assertTrue($validator->fails(), "Failed to invalidate URL: $url");
        }
    }

    /**
     * Test multiple validation rules
     */
    public function testMultipleValidationRules() {
        $data = [
            'email' => 'invalid',
            'age' => 'not a number',
            'password' => '123'
        ];

        $validator = new Validator($data);
        $validator->required('email');
        $validator->email('email');
        $validator->integer('age');
        $validator->minLength('password', 8);

        $this->assertTrue($validator->fails());
        $errors = $validator->getAllErrors();
        $this->assertGreaterThan(1, count($errors));
    }

    /**
     * Test error messages
     */
    public function testErrorMessages() {
        $validator = new Validator(['email' => 'invalid']);
        $validator->email('email');

        $this->assertTrue($validator->fails());
        $errors = $validator->getAllErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('email', strtolower($errors[0]));
    }
}
