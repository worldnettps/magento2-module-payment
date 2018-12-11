<?php
/*
Copyright 2006-2014 WorldNetTPS Ltd.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace WorldnetTPS\Payment\Model\Api;

/**
 * Base Request Class holding common functionality for Request Types.
 */
class WorldnetTPSRequest
{
    public function __construct()
    {
    }

    protected static function GetRequestHash($plainString)
    {
        return hash('sha512', $plainString);
    }

    protected static function GetFormattedDate()
    {
        return date('d-m-Y:H:i:s:000');
    }

    protected static function SendRequestToGateway($requestString, $serverUrl)
    {
        // Initialisation
        $ch = curl_init();
        // Set parameters
        curl_setopt($ch, CURLOPT_URL, $serverUrl);
        // Return a variable instead of posting it directly
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // Activate the POST method
        curl_setopt($ch, CURLOPT_POST, 1);
        // Request
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
        // execute the connection
        $result = curl_exec($ch);
        // Close it
        curl_close($ch);
        return $result;
    }
}

