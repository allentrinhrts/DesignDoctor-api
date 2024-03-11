<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\File;

class AnalysisController extends Controller
{
    /**
     * Handles a request to the OpenAI API to generate completions.
     */
    public function analyzeScreenshotByFileId(string $fileId)
    {
        $analysis = Analysis::where('file_id', $fileId)->first();
        if ($analysis) {
            return response()->json($analysis);
        }

        $file = File::findOrFail($fileId);
        $fileContents = base64_encode(file_get_contents(public_path('storage/' . $file->location)));

        try {
            $client = \OpenAi::client(env('OPENAI_API_KEY'));

            $messages = [
                [
                    'role' => 'system',
                    'content' => 'You are a seasoned web designer who specializes in user interfaces and user experience with a focus on web accessibility.'
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Examine this design. Breaking the design into sections such as "header", "main", and "footer", tell me about each section and explain how the design succeeds in some areas and fails in others. Tell me why they succeed and fail. Tell me of any suggestions on how to improve the design. If there are web accessibility flaws, let me know. Provide your response in a detailed JSON format.',
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => 'data:image/png;base64,' . $fileContents,
                            ],
                        ],
                    ],
                ],
            ];

            $response = $client->chat()->create([
                'model' => 'gpt-4-vision-preview',
                'messages' => $messages,
                'max_tokens' => 4096,
            ]);

            // Clean up the response
            $response = $client->chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a JSON expert.',
                    ],
                    [
                        'role' => 'user',
                        'content' => '
                            Please provide the following information in a plain JSON format without any Markdown or ' .
                            'code block formatting. I want the shape of the JSON object to look like the following: ' .
                            '{ "header": { "successes": ["this is an example", "this is another example"], "failures": ["this is an example", "this is another example"], "suggestions": ["this is an example", "this is another example"] }, "main": { "successes": ["this is an example", "this is another example"], "failures": ["this is an example", "this is another example"], "suggestions": ["this is an example", "this is another example"] }, "footer": { "successes": ["this is an example", "this is another example"], "failures": ["this is an example", "this is another example"], "suggestions": ["this is an example", "this is another example"] }, "accessibility": { "successes": ["this is an example", "this is another example"], "failures": ["this is an example", "this is another example"], "suggestions": ["this is an example", "this is another example"] } }. ' .
                            'Do not modify the shape of the data in any way. Do not add new keys. I want this specific shape. Replace the example text with the contents of the following: ' .
                            $response->choices[0]->message->content
                    ]
                ],
                'response_format' => [
                    'type' => 'json_object',
                ],
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }

        $content = $response->choices[0]->message->content;
        $content = stripslashes($content);
        $content = str_replace(['\n', '\t', '\r'], '', $content);

        $analysis = Analysis::create([
            'file_id' => $fileId,
            'analysis_type_id' => 1,
            'response' => $content,
        ]);

        return response()->json($analysis);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $analysis = Analysis::findOrFail($id);
        $analysis->delete();
        return response()->json(['message' => 'Analysis deleted successfully'], 200);
    }
}
