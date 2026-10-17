<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider('gemini')]
#[Model('gemini-3.5-flash')]
class DoctorNameExtractorAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are an expert handwriting transcription assistant for medical doctor reminder cards.
Your task is to analyze the provided image of a reminder card and transcribe the doctor's name written by hand after "Dr." or on the line provided (e.g. "Thanking You Dr. ___________").

Rules:
1. Extract ONLY the doctor's name or surname (e.g. "Sharma", "Rajesh", "A.K. Patel").
2. DO NOT include "Dr.", "Doctor", or greeting words in the name.
3. If the handwriting is completely blank, unwritten, or unreadable, return null for name.
4. Provide a confidence level between 0.0 and 1.0.
INSTRUCTIONS;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->nullable()->description("The transcribed doctor's name without the 'Dr.' prefix. E.g. 'Sharma' or 'Rajesh Patel'. Null if blank or unreadable."),
            'confidence' => $schema->number()->description('Confidence score of the transcription from 0.0 to 1.0.'),
        ];
    }
}
