<?php

declare(strict_types=1);

namespace SSIS\Validators;

use SSIS\Exceptions\ValidationException;

/**
 * Server-side validation for student document requests. Field keys match
 * the frontend form so errors can be shown inline.
 */
final class DocumentRequestValidator
{
    public const DOCUMENT_TYPES = [
        'Certificate of Enrollment',
        'Transcript of Records',
        'Certificate of Grades',
        'Certificate of Good Moral Character',
        'Other',
    ];

    public const DELIVERY_METHODS = ['Pickup', 'Delivery'];

    /**
     * @param array<string, mixed> $input raw request payload
     * @return array<string, mixed> normalized input
     *
     * @throws ValidationException when input is invalid (HTTP 422)
     */
    public function validate(array $input): array
    {
        $errors = [];

        $documentType = isset($input['documentType']) && is_string($input['documentType']) ? trim($input['documentType']) : '';
        $purpose = isset($input['purpose']) && is_string($input['purpose']) ? trim($input['purpose']) : '';
        $copies = $input['copies'] ?? null;
        $deliveryMethod = isset($input['deliveryMethod']) && is_string($input['deliveryMethod']) ? trim($input['deliveryMethod']) : 'Pickup';
        $instructions = isset($input['additionalInstructions']) && is_string($input['additionalInstructions']) ? trim($input['additionalInstructions']) : '';

        if ($documentType === '') {
            $errors['documentType'] = 'Please select a document type.';
        } elseif (!in_array($documentType, self::DOCUMENT_TYPES, true)) {
            $errors['documentType'] = 'That document type is not offered.';
        }

        if ($purpose === '') {
            $errors['purpose'] = 'Please state the purpose of your request.';
        } elseif (mb_strlen($purpose) > 500) {
            $errors['purpose'] = 'The purpose must be 500 characters or fewer.';
        }

        if (!is_int($copies) || $copies < 1 || $copies > 10) {
            $errors['copies'] = 'Number of copies must be between 1 and 10.';
        }

        if (!in_array($deliveryMethod, self::DELIVERY_METHODS, true)) {
            $errors['deliveryMethod'] = 'Please choose pickup or delivery.';
        }

        if (mb_strlen($instructions) > 1000) {
            $errors['additionalInstructions'] = 'Additional instructions must be 1000 characters or fewer.';
        }

        if ($errors !== []) {
            throw new ValidationException('Please review the submitted information.', $errors);
        }

        return [
            'documentType' => $documentType,
            'purpose' => $purpose,
            'copies' => $copies,
            'deliveryMethod' => $deliveryMethod,
            'additionalInstructions' => $instructions,
        ];
    }
}
