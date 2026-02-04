<?php
/**
 * Classe para tratamento centralizado de erros e exceções
 * Garante resposta consistente em toda a aplicação
 */

class ApiException extends Exception {
    protected $statusCode;
    protected $errorCode;

    public function __construct($message, $statusCode = 500, $errorCode = 'API_ERROR') {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function toJsonResponse() {
        return response()->json([
            'error' => true,
            'code' => $this->errorCode,
            'message' => $this->getMessage()
        ], $this->statusCode);
    }
}

/**
 * Exceções específicas
 */
class UnauthorizedException extends ApiException {
    public function __construct($message = 'Não autorizado') {
        parent::__construct($message, 401, 'UNAUTHORIZED');
    }
}

class ValidationException extends ApiException {
    protected $errors = [];

    public function __construct($message, $errors = []) {
        parent::__construct($message, 422, 'VALIDATION_ERROR');
        $this->errors = $errors;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function toJsonResponse() {
        return response()->json([
            'error' => true,
            'code' => $this->errorCode,
            'message' => $this->getMessage(),
            'errors' => $this->errors
        ], $this->statusCode);
    }
}

class ResourceNotFoundException extends ApiException {
    public function __construct($resource = 'Recurso') {
        parent::__construct("$resource não encontrado", 404, 'NOT_FOUND');
    }
}

class ConflictException extends ApiException {
    public function __construct($message = 'Conflito nos dados') {
        parent::__construct($message, 409, 'CONFLICT');
    }
}
?>
