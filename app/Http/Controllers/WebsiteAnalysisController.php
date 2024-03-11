<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\File;
use App\Models\Website;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class WebsiteAnalysisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $websites = Website::orderBy('created_at', 'desc')->get();

        foreach ($websites as $website) {
            $analysis = Analysis::where('website_id', $website->id)->first();
            if ($analysis) {
                $file = File::where('id', $analysis->file_id)->first();
                $website->analysis = $analysis;
                $website->file = $file;
            }
        }

        return response()->json($websites);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $url = $request->input('url');

        $model = Website::create([
            'url' => $url,
        ]);

        return response()->json(['message' => 'Website created successfully', 'website' => $model], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Website $web)
    {
        $analysis = Analysis::where('website_id', $web->id)->first();
        if ($analysis) {
            $file = File::where('id', $analysis->file_id)->first();
            return response()->json(['analysis' => $analysis, 'website' => $web, 'file' => $file]);
        }

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
                            'type' => 'text',
                            'text' => $web->url,
                        ],
                    ],
                ],
            ];

            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
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

        // Gets a screenshot of the website
        $hostname = parse_url($web->url, PHP_URL_HOST);
        $date = date('Y-m-d_H:i:s');
        $path = storage_path() . '/app/public/uploads/' . $hostname . '_' . $date . '.png';
        Browsershot::url($web->url)
            ->setOption('landscape', true)
            ->windowSize(1600, 1024)
            ->waitUntilNetworkIdle()
            ->save($path);

        $file = File::create([
            'name' => $hostname . '_' . $date . '.png',
            'location' => '/uploads/' . $hostname . '_' . $date . '.png'
        ]);

        $analysis = Analysis::create([
            'analysis_type_id' => 2,
            'website_id' => $web->id,
            'file_id' => $file->id,
            'response' => $content,
        ]);

        return response()->json([$analysis, $web, $file]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Website $web)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Website $web)
    {
        //
    }
}
