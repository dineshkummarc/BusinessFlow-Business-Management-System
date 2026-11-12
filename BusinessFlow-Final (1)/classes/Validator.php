<?php
/**
 * Validator
 * Small reusable helper for cleaning and validating user input.
 */
class Validator
{
    private array $errors = [];

    public static function clean(?string $value): string
    {
        return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    public function required(string $field, $value, string $label): self
    {
        if ($value === null || trim((string)$value) === '') {
            $this->errors[$field] = "$label is required.";
        }
        return $this;
    }

    public function email(string $field, $value, string $label): self
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$label must be a valid email address.";
        }
        return $this;
    }

    public function minLength(string $field, $value, int $length, string $label): self
    {
        if (!empty($value) && strlen((string)$value) < $length) {
            $this->errors[$field] = "$label must be at least $length characters.";
        }
        return $this;
    }

    public function numeric(string $field, $value, string $label): self
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->errors[$field] = "$label must be a number.";
        }
        return $this;
    }

    public function range(string $field, $value, float $min, float $max, string $label): self
    {
        if ($value !== null && $value !== '' && is_numeric($value)) {
            $num = (float) $value;
            if ($num < $min || $num > $max) {
                $this->errors[$field] = "$label must be between $min and $max.";
            }
        }
        return $this;
    }

    public function matches(string $field, $value, $other, string $label): self
    {
        if ($value !== $other) {
            $this->errors[$field] = "$label does not match.";
        }
        return $this;
    }

    /**
     * Phase 3 — logical date-order validation (e.g. a due date must not
     * fall before its issue date, a deadline must not precede its start
     * date). Skips silently if either date is empty, since `required()`
     * already reports that separately.
     */
    public function dateOnOrAfter(string $field, ?string $laterDate, ?string $earlierDate, string $label, string $compareLabel): self
    {
        if (!empty($laterDate) && !empty($earlierDate)) {
            $later = strtotime($laterDate);
            $earlier = strtotime($earlierDate);
            if ($later !== false && $earlier !== false && $later < $earlier) {
                $this->errors[$field] = "$label cannot be earlier than $compareLabel.";
            }
        }
        return $this;
    }

    public function fails(): bool
    {
        return count($this->errors) > 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors ? array_values($this->errors)[0] : null;
    }
}
