<?php

config(
    ['AMS.provider'=>
        [
            'name' => 'adsense',
            'url'  => 'https://www.googleapis.com/adsense/v1.4/reports',
            'date_format' => 'Y-m-d',
            'auth' => 'oauth', //basic, oauth, etc
            'scope'=> 'https://www.googleapis.com/auth/adsense.readonly',
            'redirectUri' => 
                (!empty($_SERVER['HTTPS'])
                    ? 'https://' 
                    : 'http://'
                )
                . (!empty($_SERVER['HTTP_HOST'])
                    ? $_SERVER['HTTP_HOST'] 
                    : 'limitless-sea-32755.herokuapp.com'
                )
                .'/oauth/adsense',
            'serviceAccountFile' => realpath(dirname(__FILE__)).'/credentials.json',
            'token'=> [
                    'access_token'=> 'ya29.GlvkA_1rEmH-HFPTx56BxsGO43BX6O6dUvNzLO0FSKNw3iqoBSrjqi_61N7URRCnyCCOnz6EpipfTJsR3D-FA2ye73ZZ6GLalvYc_vrOUKRDRugXGdPX9lc-bmSh',
                    'token_type'=> 'Bearer',
                    'expires_in'=> 3600,
                    'refresh_token'=> '1/wvex4l8GJcIR7-UjwB5tlEnne2qgeUHtMrtOr-rdrQ8',
                    'created'=> 1485836663
            ],
            'inventaire' => 'AMS Market Place'
        ]
    ]
);