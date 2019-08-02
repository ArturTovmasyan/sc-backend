<?php
namespace App\Model;

use Symfony\Component\HttpFoundation\Response;

class ResponseCode
{
    /**
     * Success codes
     */
    const RECOVERY_LINK_SENT_TO_EMAIL            = 230;
    const INVITATION_LINK_SENT_TO_EMAIL          = 231;
    const RECOVERY_LINK_INVALID                  = 232;
    const ACTIVATION_LINK_INVALID                = 233;

    /**
     * Error codes
     */
    const USER_NOT_FOUND_EXCEPTION               = 609;
    const VALIDATION_ERROR_EXCEPTION             = 610;
    const GRID_OPTIONS_NOT_FOUND_EXCEPTION       = 611;
    const CUSTOMER_NOT_FOUND_EXCEPTION           = 612;
    const JOB_NOT_FOUND_EXCEPTION                = 613;
    const VHOST_NOT_FOUND_EXCEPTION              = 614;
    const ROLE_NOT_FOUND_EXCEPTION               = 615;
    const INVALID_GRANT_CONFIG                   = 617;
    const ROLE_SYNC_ERROR                        = 618;
    const HELP_OBJECT_NOT_FOUND_EXCEPTION        = 619;
    const HELP_CATEGORY_NOT_FOUND_EXCEPTION      = 620;

    /**
     * @var array
     */
    public static $titles = [
        // success
        self::RECOVERY_LINK_SENT_TO_EMAIL                 => ['httpCode' => Response::HTTP_CREATED,     'message' => 'Password recovery link sent, please check email.'],
        self::INVITATION_LINK_SENT_TO_EMAIL               => ['httpCode' => Response::HTTP_CREATED,     'message' => 'Invitation sent to email address, please check email.'],
        self::RECOVERY_LINK_INVALID                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'The recovery link invalid or expired.'],
        self::ACTIVATION_LINK_INVALID                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'The activation link invalid or expired.'],
        // errors
        self::USER_NOT_FOUND_EXCEPTION                    => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User not found.'],
        self::VALIDATION_ERROR_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Validation error.'],
        self::GRID_OPTIONS_NOT_FOUND_EXCEPTION            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Grid options not found.'],
        self::CUSTOMER_NOT_FOUND_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Customer not found.'],
        self::JOB_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Job not found.'],
        self::VHOST_NOT_FOUND_EXCEPTION                   => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Vhost not found.'],
        self::ROLE_NOT_FOUND_EXCEPTION                    => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Role not found.'],
        self::HELP_OBJECT_NOT_FOUND_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Help Object not found.'],
        self::HELP_CATEGORY_NOT_FOUND_EXCEPTION           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Help Category not found.'],
        self::INVALID_GRANT_CONFIG                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invalid grant configuration.'],
        self::ROLE_SYNC_ERROR                             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Error occurred during synchronization of customer roles.'],
    ];
}
