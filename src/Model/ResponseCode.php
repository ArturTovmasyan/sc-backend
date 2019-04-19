<?php
namespace App\Model;

use Symfony\Component\HttpFoundation\Response;

class ResponseCode
{
    /**
     * Success codes
     */
    const RECOVERY_LINK_SENT_TO_EMAIL   = 230;
    const INVITATION_LINK_SENT_TO_EMAIL = 231;
    const RECOVERY_LINK_INVALID         = 232;
    const ACTIVATION_LINK_INVALID       = 233;

    /**
     * Error codes
     */
    const USER_NOT_FOUND_EXCEPTION               = 609;
    const VALIDATION_ERROR_EXCEPTION             = 610;
    const GRID_OPTIONS_NOT_FOUND_EXCEPTION       = 611;

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
    ];
}
