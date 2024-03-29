<?php

    require_once "models/get.model.php";
    require_once "models/post.model.php";
    require_once "models/connection.php";

    require_once "vendor/autoload.php";
    use Firebase\JWT\JWT;

    require_once "models/put.model.php";

    class PostController{

        /*======================================================
            TODO: Peticiones POST para crear datos
        ======================================================*/

        static public function postData($table, $data){

            $response = PostModel::postData($table, $data);

            $return = new PostController();
            $return -> fncResponse($response, null, null);

        }

        /*======================================================
            TODO: Peticiones POST para registrar usuario
        ======================================================*/

        static public function postRegister($table, $data, $suffix){

            if(isset($data["password_".$suffix]) && $data["password_".$suffix] != null){

                /*================================================================================
                    TODO: Hash con Blowfish (CRYPT_BLOWFISH), utilizar de la siguiente manera
                            '$2a$07$  Lo que tu quieras  $'
                ================================================================================*/

                $crypt = crypt($data["password_".$suffix], '$2a$07$azybxcags23425sdg23sdfhsd$');

                $data["password_".$suffix] = $crypt;
                $response = PostModel::postData($table, $data);

                $return = new PostController();
                $return -> fncResponse($response, null, $suffix);

            }else{

                /*======================================================
                    TODO: Registro de usuarios desde APP externas
                ======================================================*/

                $response = PostModel::postData($table, $data);

                if(isset($response["comment"]) && $response["comment"] == "The pocess was successful"){

                    /*==============================================================
                        TODO: Validar que el usuario exista en la base de datos
                    ==============================================================*/

                    $response = GetModel::getDataFilter($table, "*", "email_".$suffix, $data["email_".$suffix], null, null, null, null);

                    if(!empty($response)){

                        $token = Connection::jwt($response[0]->{"id_".$suffix}, $response[0]->{"email_".$suffix});

                        $jwt = JWT::encode($token, "dfhsdfg34dfchs4xgsrsdry46");

                        /*=====================================================================
                            TODO: Actualizamos la base de datos con el Token del Usuario
                        =====================================================================*/

                        $data = array(

                            "token_".$suffix => $jwt,
                            "token_exp_".$suffix => $token["exp"]
                        );

                        $update = PutModel::putData($table, $data, $response[0]->{"id_".$suffix}, "id_".$suffix);

                        if(isset($update["comment"]) && $update["comment"] == "The pocess was successful"){

                            $response[0]->{"token_".$suffix} = $jwt;
                            $response[0]->{"token_exp_".$suffix} = $token["exp"];

                            $return = new PostController();
                            $return -> fncResponse($response, null, $suffix);

                        }
                    }

                }

            }

        }

        /*======================================================
            TODO: Peticiones POST para Login de usuario
        ======================================================*/

        static public function postLogin($table, $data, $suffix){

            /*==============================================================
                TODO: Validar que el usuario exista en la base de datos
            ==============================================================*/

            $response = GetModel::getDataFilter($table, "*", "email_".$suffix, $data["email_".$suffix], null, null, null, null);

            if(!empty($response)){

                if($response[0]->{"password_".$suffix} != null){

                    /*==============================================================
                        TODO: Encriptamos la contraseña
                    ==============================================================*/

                    $crypt = crypt($data["password_".$suffix], '$2a$07$azybxcags23425sdg23sdfhsd$');

                    if($response[0]->{"password_".$suffix} == $crypt){

                        $token = Connection::jwt($response[0]->{"id_".$suffix}, $response[0]->{"email_".$suffix});

                        $jwt = JWT::encode($token, "dfhsdfg34dfchs4xgsrsdry46");

                        /*=====================================================================
                            TODO: Actualizamos la base de datos con el Token del Usuario
                        =====================================================================*/

                        $data = array(

                            "token_".$suffix => $jwt,
                            "token_exp_".$suffix => $token["exp"]
                        );

                        $update = PutModel::putData($table, $data, $response[0]->{"id_".$suffix}, "id_".$suffix);

                        if(isset($update["comment"]) && $update["comment"] == "The pocess was successful"){

                            $response[0]->{"token_".$suffix} = $jwt;
                            $response[0]->{"token_exp_".$suffix} = $token["exp"];

                            $return = new PostController();
                            $return -> fncResponse($response, null, $suffix);

                        }

                    }else{

                        $response = null;
                        $return = new PostController();
                        $return -> fncResponse($response, "Wrong password", $suffix);

                    }

                }else{

                    /*=============================================================================
                        TODO: Actualizamos el Token para usuarios logueados desde APP externas
                    =============================================================================*/

                    $token = Connection::jwt($response[0]->{"id_".$suffix}, $response[0]->{"email_".$suffix});

                    $jwt = JWT::encode($token, "dfhsdfg34dfchs4xgsrsdry46");

                    $data = array(

                        "token_".$suffix => $jwt,
                        "token_exp_".$suffix => $token["exp"]
                    );

                    $update = PutModel::putData($table, $data, $response[0]->{"id_".$suffix}, "id_".$suffix);

                    if(isset($update["comment"]) && $update["comment"] == "The pocess was successful"){

                        $response[0]->{"token_".$suffix} = $jwt;
                        $response[0]->{"token_exp_".$suffix} = $token["exp"];

                        $return = new PostController();
                        $return -> fncResponse($response, null, $suffix);

                    }


                }

            }else{

                $response = null;
                $return = new PostController();
                $return -> fncResponse($response, "Wrong email", $suffix);
            }

        }

        /*======================================================
            TODO: Respuestas del controlador
        ======================================================*/

        public function fncResponse($response, $error, $suffix){

            if(!empty($response)){

                /*======================================================
                    TODO: Quitar la contraseña de la respuesta
                ======================================================*/

                if(isset($response[0]->{"password_".$suffix})){

                    unset($response[0]->{"password_".$suffix});

                }

                $json = array(

                    'status' => 200,
                    'results' => $response

                );

            }else{

                if($error != null){

                    $json = array(

                        'status' => 400,
                        'results' => $error

                    );

                }else{

                    $json = array(

                        'status' => 404,
                        'results' => 'Not Found',
                        'method' => 'post'

                    );

                }

            }

            echo json_encode($json, http_response_code($json["status"]));

        }

    }

?>