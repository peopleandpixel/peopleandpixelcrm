<?php
declare(strict_types=1);

use App\Util\Sanitizer;

require __DIR__ . '/../vendor/autoload.php';

final class SanitizerSecurityTest
{
    private int $passed = 0;
    private int $failed = 0;

    private function assertSame($expected, $actual, string $message): void
    {
        if ($expected === $actual) {
            $this->passed++;
        } else {
            $this->failed++;
            fwrite(STDERR, "Assertion failed: {$message}\nExpected: " . var_export($expected, true) . "\nActual:   " . var_export($actual, true) . "\n");
        }
    }

    private function assertTrue(bool $cond, string $message): void
    {
        if ($cond) { $this->passed++; } else { $this->failed++; fwrite(STDERR, "Assertion failed: {$message}\n"); }
    }

    private function testStripsHtmlTags(): void
    {
        $in = "<div>Hello <script>alert('x')</script><b>World</b>!";
        $out = Sanitizer::string($in);
        // strip_tags removes tags but keeps inner text from script; ensure no angle brackets remain
        $this->assertTrue(strpos($out, '<') === false && strpos($out, '>') === false, 'Sanitizer::string should strip HTML tags.');
        $this->assertSame("Hello alert('x')World!", $out, 'Sanitizer::string should keep text without tags.');
    }

    private function testRemovesControlChars(): void
    {
        $in = "he\x00llo\x07\x1F\x7Fworld\nok";
        $out = Sanitizer::string($in);
        $this->assertSame("helloworld\nok", $out, 'Sanitizer::string should remove control characters except common whitespace.');
    }

    private function testEmailNormalization(): void
    {
        $in = "  USER@Example.COM  ";
        $out = Sanitizer::email($in);
        $this->assertSame('user@example.com', $out, 'Sanitizer::email should trim and lowercase.');
        $in2 = "<img src=x onerror=alert(1)>User@Site.com";
        $out2 = Sanitizer::email($in2);
        $this->assertTrue(strpos($out2, '<') === false && strpos($out2, '>') === false, 'Sanitizer::email should strip tags.');
    }

    private function testEscapeHtml(): void
    {
        $in = "<script>\"x\" & 'y'</script>";
        $out = Sanitizer::escapeHtml($in);
        $this->assertSame('&lt;script&gt;&quot;x&quot; &amp; &#039;y&#039;&lt;/script&gt;', $out, 'escapeHtml should encode special characters.');
    }

    private function testNonNegativeInt(): void
    {
        $this->assertSame(5, Sanitizer::int(5), 'Sanitizer::int should keep positive values.');
        $this->assertSame(0, Sanitizer::int(-10), 'Sanitizer::int should clamp to non-negative.');
        $this->assertSame(0, Sanitizer::int(null), 'Sanitizer::int should handle null.');
    }

    public function run(): int
    {
        $this->testStripsHtmlTags();
        $this->testRemovesControlChars();
        $this->testEmailNormalization();
        $this->testEscapeHtml();
        $this->testNonNegativeInt();
        $total = $this->passed + $this->failed;
        echo sprintf("SanitizerSecurityTest: %d passed, %d failed (of %d)\n", $this->passed, $this->failed, $total);
        return $this->failed === 0 ? 0 : 1;
    }
}

$test = new SanitizerSecurityTest();
exit($test->run());
