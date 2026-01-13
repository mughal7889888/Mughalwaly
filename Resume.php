<?php
declare(strict_types=1);

/**
 * Simple Resume value object.
 * Holds all resume data in a structured way and exposes convenience methods
 * for working with skills.
 */
class Resume
{
    public string $name;
    public string $email;
    public string $phone;
    public string $address;
    public string $educationSchool;
    public string $educationDegree;
    public string $educationYear;
    public string $workCompany;
    public string $workPosition;
    public string $workYears;
    public string $skillsRaw;
    public ?string $profilePicture;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->name             = (string)($data['name'] ?? '');
        $this->email            = (string)($data['email'] ?? '');
        $this->phone            = (string)($data['phone'] ?? '');
        $this->address          = (string)($data['address'] ?? '');
        $this->educationSchool  = (string)($data['education_school'] ?? '');
        $this->educationDegree  = (string)($data['education_degree'] ?? '');
        $this->educationYear    = (string)($data['education_year'] ?? '');
        $this->workCompany      = (string)($data['work_company'] ?? '');
        $this->workPosition     = (string)($data['work_position'] ?? '');
        $this->workYears        = (string)($data['work_years'] ?? '');
        $this->skillsRaw        = (string)($data['skills'] ?? '');
        $this->profilePicture   = $data['profile_picture'] ?? null;
    }

    /**
     * Convert comma-separated skills string into an array of trimmed skill names.
     *
     * @return string[]
     */
    public function getSkillsList(): array
    {
        if ($this->skillsRaw === '') {
            return [];
        }

        $skills = array_map('trim', explode(',', $this->skillsRaw));

        // Remove any empty entries
        return array_values(array_filter($skills, static function (string $skill): bool {
            return $skill !== '';
        }));
    }
}