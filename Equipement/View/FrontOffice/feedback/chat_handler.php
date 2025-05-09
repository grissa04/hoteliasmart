<?php
header('Content-Type: application/json');

// API configuration
$API_KEY = 'AIzaSyDlBlYfEhRaCDNn44xpIfZ0NQKoLqEz8zw';
$API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $message = $data['message'] ?? '';

    if (empty($message)) {
        echo json_encode(['error' => 'Message is required']);
        exit;
    }

    // Initialize cURL session
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $API_URL . '?key=' . $API_KEY,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => 'You are a helpful hotel assistant handling reclamations and feedback. Provide professional, courteous responses.']
                    ]
                ],
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $message]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 150
            ]
        ])
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $info = curl_getinfo($curl);
    
    if ($err) {
        error_log("cURL Error: " . $err);
        error_log("cURL Info: " . json_encode($info));
        
        // Fallback response
        $responses = [
            "I apologize, but I'm having trouble connecting to the service. How else may I assist you?",
            "I understand your concern. Please let me help you with your inquiry.",
            "Thank you for your patience. Could you please provide more details about your request?",
            "I'm here to help with your hotel-related questions. What specific information do you need?",
            "I'd be happy to assist you with any concerns about our hotel services."
        ];
        
        echo json_encode([
            'response' => $responses[array_rand($responses)]
        ]);
        exit;
    }

    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        echo json_encode([
            'response' => $result['candidates'][0]['content']['parts'][0]['text']
        ]);
    } else {
        error_log("API Response Error: " . $response);
        // Fallback to simple response
        echo json_encode([
            'response' => "I'm here to help with your hotel-related questions. What specific information do you need?"
        ]);
    }
}
?>