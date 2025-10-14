<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VerificationTemplate;
use App\Models\DeactivationRequest;
use App\Models\OtpVerification;
use App\Models\Drivers;
use Illuminate\Support\Facades\Route;

class ApiDocumentationController extends Controller
{
    /**
     * Generate JSON schema documentation for APIs.
     *
     * @param string $api
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSchema($api = null)
    {
        $schemas = [
            'verification' => $this->getVerificationApiSchema(),
            'deactivation' => $this->getDeactivationApiSchema(),
            'otp' => $this->getOtpApiSchema(),
            'driver_history_check' => $this->getDriverHistoryCheckApiSchema(),
        ];

        if ($api && isset($schemas[$api])) {
            return response()->json([
                'api' => $api,
                'schema' => $schemas[$api],
                'generated_at' => now()->toISOString()
            ]);
        }

        return response()->json([
            'apis' => $schemas,
            'generated_at' => now()->toISOString()
        ]);
    }

    /**
     * Get verification API schema.
     *
     * @return array
     */
    private function getVerificationApiSchema()
    {
        $templates = VerificationTemplate::active()->ordered()->get();

        return [
            'name' => 'Verification API',
            'version' => '1.0.0',
            'description' => 'API for handling various verification processes',
            'base_url' => config('app.url') . '/api',
            'authentication' => [
                'type' => 'Bearer Token',
                'header' => 'Authorization: Bearer {token}'
            ],
            'endpoints' => [
                [
                    'method' => 'GET',
                    'path' => '/verification/templates',
                    'description' => 'Get all active verification templates',
                    'parameters' => [
                        'query' => [
                            'type' => ['string', 'optional', 'Filter by template type']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 200,
                            'schema' => [
                                'templates' => 'array of verification templates',
                                'templates[].id' => 'integer',
                                'templates[].name' => 'string',
                                'templates[].type' => 'string',
                                'templates[].description' => 'string',
                                'templates[].template_data' => 'object'
                            ]
                        ]
                    ]
                ],
                [
                    'method' => 'POST',
                    'path' => '/verification/{templateId}/start',
                    'description' => 'Start a verification process',
                    'parameters' => [
                        'path' => [
                            'templateId' => ['integer', 'required', 'Verification template ID']
                        ],
                        'body' => [
                            'user_id' => ['integer', 'required', 'User ID to verify'],
                            'data' => ['object', 'required', 'Verification data based on template']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 201,
                            'schema' => [
                                'verification_id' => 'string',
                                'status' => 'string',
                                'expires_at' => 'datetime'
                            ]
                        ]
                    ]
                ],
                [
                    'method' => 'GET',
                    'path' => '/verification/{verificationId}/status',
                    'description' => 'Check verification status',
                    'parameters' => [
                        'path' => [
                            'verificationId' => ['string', 'required', 'Verification ID']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 200,
                            'schema' => [
                                'verification_id' => 'string',
                                'status' => 'string',
                                'result' => 'object',
                                'completed_at' => 'datetime'
                            ]
                        ]
                    ]
                ]
            ],
            'verification_types' => $templates->map(function ($template) {
                return [
                    'type' => $template->type,
                    'name' => $template->name,
                    'required_fields' => $template->getRequiredFields(),
                    'validation_rules' => $template->getValidationRules(),
                    'api_endpoints' => $template->getApiEndpoints()
                ];
            })->toArray()
        ];
    }

    /**
     * Get deactivation API schema.
     *
     * @return array
     */
    private function getDeactivationApiSchema()
    {
        return [
            'name' => 'Deactivation API',
            'version' => '1.0.0',
            'description' => 'API for managing user deactivation requests',
            'base_url' => config('app.url') . '/api',
            'authentication' => [
                'type' => 'Bearer Token',
                'header' => 'Authorization: Bearer {token}'
            ],
            'endpoints' => [
                [
                    'method' => 'POST',
                    'path' => '/deactivation/request',
                    'description' => 'Submit a deactivation request',
                    'parameters' => [
                        'body' => [
                            'user_type' => ['string', 'required', 'Type of user (driver/company)'],
                            'user_id' => ['integer', 'required', 'User ID'],
                            'reason' => ['string', 'required', 'Reason for deactivation'],
                            'additional_notes' => ['string', 'optional', 'Additional notes']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 201,
                            'schema' => [
                                'request_id' => 'integer',
                                'status' => 'string',
                                'message' => 'string',
                                'requires_otp' => 'boolean'
                            ]
                        ]
                    ]
                ],
                [
                    'method' => 'GET',
                    'path' => '/deactivation/requests',
                    'description' => 'Get deactivation requests (admin only)',
                    'parameters' => [
                        'query' => [
                            'status' => ['string', 'optional', 'Filter by status'],
                            'user_type' => ['string', 'optional', 'Filter by user type'],
                            'page' => ['integer', 'optional', 'Page number'],
                            'per_page' => ['integer', 'optional', 'Items per page']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 200,
                            'schema' => [
                                'requests' => 'array of deactivation requests',
                                'requests[].id' => 'integer',
                                'requests[].user_type' => 'string',
                                'requests[].user_id' => 'integer',
                                'requests[].status' => 'string',
                                'requests[].reason' => 'string',
                                'requests[].created_at' => 'datetime',
                                'pagination' => 'object'
                            ]
                        ]
                    ]
                ],
                [
                    'method' => 'POST',
                    'path' => '/deactivation/{requestId}/review',
                    'description' => 'Review deactivation request (admin only)',
                    'parameters' => [
                        'path' => [
                            'requestId' => ['integer', 'required', 'Request ID']
                        ],
                        'body' => [
                            'action' => ['string', 'required', 'approve/reject'],
                            'notes' => ['string', 'optional', 'Review notes']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 200,
                            'schema' => [
                                'request_id' => 'integer',
                                'status' => 'string',
                                'message' => 'string'
                            ]
                        ]
                    ]
                ]
            ],
            'statuses' => ['pending', 'approved', 'rejected', 'completed'],
            'user_types' => ['driver', 'company']
        ];
    }

    /**
     * Get OTP API schema.
     *
     * @return array
     */
    private function getOtpApiSchema()
    {
        return [
            'name' => 'OTP Verification API',
            'version' => '1.0.0',
            'description' => 'API for OTP generation and verification',
            'base_url' => config('app.url') . '/api',
            'authentication' => [
                'type' => 'Bearer Token',
                'header' => 'Authorization: Bearer {token}'
            ],
            'endpoints' => [
                [
                    'method' => 'POST',
                    'path' => '/otp/generate',
                    'description' => 'Generate OTP for verification',
                    'parameters' => [
                        'body' => [
                            'user_type' => ['string', 'required', 'Type of user'],
                            'user_id' => ['integer', 'required', 'User ID'],
                            'type' => ['string', 'required', 'OTP type (email/sms)'],
                            'purpose' => ['string', 'required', 'Purpose (verification/deactivation)']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 200,
                            'schema' => [
                                'otp_id' => 'string',
                                'expires_at' => 'datetime',
                                'message' => 'string'
                            ]
                        ]
                    ]
                ],
                [
                    'method' => 'POST',
                    'path' => '/otp/verify',
                    'description' => 'Verify OTP code',
                    'parameters' => [
                        'body' => [
                            'otp_id' => ['string', 'required', 'OTP ID'],
                            'code' => ['string', 'required', 'OTP code'],
                            'user_id' => ['integer', 'required', 'User ID']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 200,
                            'schema' => [
                                'verified' => 'boolean',
                                'message' => 'string',
                                'expires_at' => 'datetime'
                            ]
                        ]
                    ]
                ],
                [
                    'method' => 'POST',
                    'path' => '/otp/resend',
                    'description' => 'Resend OTP',
                    'parameters' => [
                        'body' => [
                            'otp_id' => ['string', 'required', 'Original OTP ID']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 200,
                            'schema' => [
                                'otp_id' => 'string',
                                'expires_at' => 'datetime',
                                'message' => 'string'
                            ]
                        ]
                    ]
                ]
            ],
            'types' => ['email', 'sms'],
            'purposes' => ['verification', 'deactivation', 'password_reset'],
            'settings' => [
                'max_attempts' => 3,
                'expiry_minutes' => 10,
                'resend_cooldown_minutes' => 1
            ]
        ];
    }

    /**
     * Get driver history check API schema.
     *
     * @return array
     */
    private function getDriverHistoryCheckApiSchema()
    {
        return [
            'name' => 'Driver History Check API',
            'version' => '1.0.0',
            'description' => 'API for checking driver employment and background history',
            'base_url' => config('app.url') . '/api',
            'authentication' => [
                'type' => 'Bearer Token',
                'header' => 'Authorization: Bearer {token}'
            ],
            'endpoints' => [
                [
                    'method' => 'GET',
                    'path' => '/driver/{driverId}/history',
                    'description' => 'Get driver history and background check',
                    'parameters' => [
                        'path' => [
                            'driverId' => ['integer', 'required', 'Driver ID']
                        ],
                        'query' => [
                            'include_employment' => ['boolean', 'optional', 'Include employment history'],
                            'include_background' => ['boolean', 'optional', 'Include background check'],
                            'include_performance' => ['boolean', 'optional', 'Include performance metrics']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 200,
                            'schema' => [
                                'driver_id' => 'integer',
                                'verification_status' => 'string',
                                'history' => [
                                    'employment' => 'array of employment records',
                                    'background_check' => 'object',
                                    'performance_metrics' => 'object',
                                    'last_updated' => 'datetime'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'method' => 'POST',
                    'path' => '/driver/{driverId}/background-check',
                    'description' => 'Initiate background check for driver',
                    'parameters' => [
                        'path' => [
                            'driverId' => ['integer', 'required', 'Driver ID']
                        ],
                        'body' => [
                            'check_types' => ['array', 'required', 'Types of checks to perform'],
                            'consent_given' => ['boolean', 'required', 'User consent']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 202,
                            'schema' => [
                                'check_id' => 'string',
                                'status' => 'string',
                                'estimated_completion' => 'datetime',
                                'message' => 'string'
                            ]
                        ]
                    ]
                ],
                [
                    'method' => 'GET',
                    'path' => '/driver/{driverId}/background-check/{checkId}',
                    'description' => 'Get background check results',
                    'parameters' => [
                        'path' => [
                            'driverId' => ['integer', 'required', 'Driver ID'],
                            'checkId' => ['string', 'required', 'Check ID']
                        ]
                    ],
                    'response' => [
                        'success' => [
                            'status' => 200,
                            'schema' => [
                                'check_id' => 'string',
                                'status' => 'string',
                                'results' => 'object',
                                'completed_at' => 'datetime'
                            ]
                        ]
                    ]
                ]
            ],
            'check_types' => [
                'criminal_background',
                'driving_record',
                'employment_verification',
                'reference_check',
                'drug_test'
            ],
            'history_components' => [
                'employment_history',
                'performance_records',
                'incident_reports',
                'training_records',
                'certifications'
            ]
        ];
    }

    /**
     * Generate OpenAPI/Swagger documentation.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOpenApiSpec()
    {
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'DriveLink API',
                'version' => '1.0.0',
                'description' => 'API for driver verification, deactivation, and monitoring'
            ],
            'servers' => [
                [
                    'url' => config('app.url') . '/api',
                    'description' => 'Production server'
                ]
            ],
            'security' => [
                [
                    'bearerAuth' => []
                ]
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer'
                    ]
                ],
                'schemas' => $this->getCommonSchemas()
            ],
            'paths' => $this->getOpenApiPaths()
        ];

        return response()->json($spec);
    }

    /**
     * Get common schemas for OpenAPI spec.
     *
     * @return array
     */
    private function getCommonSchemas()
    {
        return [
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string'],
                    'errors' => ['type' => 'object']
                ]
            ],
            'DeactivationRequest' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'user_type' => ['type' => 'string', 'enum' => ['driver', 'company']],
                    'user_id' => ['type' => 'integer'],
                    'status' => ['type' => 'string', 'enum' => ['pending', 'approved', 'rejected']],
                    'reason' => ['type' => 'string'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time']
                ]
            ],
            'OtpVerification' => [
                'type' => 'object',
                'properties' => [
                    'otp_id' => ['type' => 'string'],
                    'type' => ['type' => 'string', 'enum' => ['email', 'sms']],
                    'is_verified' => ['type' => 'boolean'],
                    'expires_at' => ['type' => 'string', 'format' => 'date-time']
                ]
            ]
        ];
    }

    /**
     * Get OpenAPI paths.
     *
     * @return array
     */
    private function getOpenApiPaths()
    {
        return [
            '/verification/templates' => [
                'get' => [
                    'summary' => 'Get verification templates',
                    'responses' => [
                        '200' => [
                            'description' => 'List of verification templates',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'templates' => [
                                                'type' => 'array',
                                                'items' => ['$ref' => '#/components/schemas/VerificationTemplate']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            '/deactivation/request' => [
                'post' => [
                    'summary' => 'Submit deactivation request',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'user_type' => ['type' => 'string'],
                                        'user_id' => ['type' => 'integer'],
                                        'reason' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'Deactivation request created',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/DeactivationRequest']
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            '/otp/generate' => [
                'post' => [
                    'summary' => 'Generate OTP',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'user_type' => ['type' => 'string'],
                                        'user_id' => ['type' => 'integer'],
                                        'type' => ['type' => 'string'],
                                        'purpose' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'OTP generated',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/OtpVerification']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
