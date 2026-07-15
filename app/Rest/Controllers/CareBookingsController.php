<?php

namespace App\Rest\Controllers;

class CareBookingsController extends Controller
{
    /**
     * The resource the controller corresponds to.
     *
     * @var class-string<\Lomkit\Rest\Http\Resource>
     */
    public static $resource = \App\Rest\Resources\CareBookingResource::class;
}
