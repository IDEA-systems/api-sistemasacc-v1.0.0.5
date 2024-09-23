<?php

require 'controller/database/users/Login.php';
require 'controller/database/users/Read.php';
require 'controller/database/users/User.php';


Flight::route("POST /@usuario_id/user/", function ($usuario_id) {
    require 'controller/database/users/create-user.php';
});

Flight::route("GET /@usuario_id/user", function ($usuario_id) {
    require 'controller/database/users/user-byid.php';
});

Flight::route("GET /@usuario_id/user/details", function ($usuario_id) {
    require 'controller/database/users/user-details.php';
});

Flight::route("POST /@usuario_id/user/profile/update", function ($usuario_id) {
    require 'controller/database/users/update-profile.php';
});

Flight::route("GET /@usuario_id/user/types", function ($usuario_id) {
    require 'controller/database/users/user-types.php';
});

Flight::route('POST /login', function () {
    require 'controller/database/users/session-init.php';
});

Flight::route('GET /login/logout/@usuario_id', function ($usuario_id) {
    require 'controller/database/users/abort-session.php';
});

Flight::route('GET /session', function () {
    include_once ('controller/database/session/session.php');
});
