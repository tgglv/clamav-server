<?php

class Scanner {

	/**
	 * Validate file before it is uploaded.
	 *
	 * Ends process and exits with error for invalid files.
	 */
	public function validate_file() {
		if ( empty( $_FILES['file'] ) ) {
			$this->json_response( 'Missing file!', 400 );
		}

		$uploaded_filename = $_FILES['file']['tmp_name'];

		$this->check_upload_error();

		$type          = $_FILES['file']['type'];
		$allowed_types = [
			'application/zip',
			'application/octet-stream',
			'application/x-zip-compressed',
		];
		if ( ! in_array( $type, $allowed_types, true ) ) {
			$this->json_response( sprintf( 'Bad file type: %s.', $type ), 400 );
		}
		if ( ! $this->is_file_clean( $uploaded_filename ) ) {
			$this->json_response( 'File is infected', 400 );
		}
		$this->json_response( 'File is clean' );
	}

	public function is_file_clean( $filename ) {
		$output = shell_exec( "clamscan --infected --no-summary {$filename}" );
		unlink( $filename );
		return empty($output);
	}

	/**
	 * Check if there was an upload error.
	 */
	public function check_upload_error() {
		if ( is_uploaded_file( $_FILES['file']['tmp_name'] ) ) {
			return;
		}

		$error_code = ! empty( $_FILES['file']['error'] )
			? $_FILES['file']['error']
			: '';

		// Based on http://php.net/manual/en/features.file-upload.errors.php#89374.
		switch ( $error_code ) {
			case UPLOAD_ERR_INI_SIZE:
				$reason = 'The uploaded file exceeds the `upload_max_filesize` directive in `php.ini`.';
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$reason = 'The uploaded file exceeds the `MAX_FILE_SIZE` directive that was specified in the HTML form.';
				break;
			case UPLOAD_ERR_PARTIAL:
				$reason = 'The uploaded file was only partially uploaded.';
				break;
			case UPLOAD_ERR_NO_FILE:
				$reason = 'No file was uploaded.';
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$reason = 'Missing a temporary folder.';
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$reason = 'Failed to write file to disk.';
				break;
			case UPLOAD_ERR_EXTENSION:
				$reason = 'File upload stopped by extension.';
				break;
			default:
				$reason = 'Unknown upload error.';
		}

		$this->logmsg( 'File upload failed: ' . $reason, LOG_ERR );
		$this->json_response( 'Upload failed.', 500 );
	}

	/**
	 * Send a JSON response along with an HTTP status code.
	 *
	 * @param string|array $message
	 * @param int $http_code
	 */
	function json_response( $message, $http_code = 200 ) {
		http_response_code( $http_code );

		$result = array(
			'success' => 200 == $http_code,
		);

		if ( is_array( $message ) ) {
			$result = array_merge( $message, $result );
		} else {
			$result['message'] = $message;
		}

		echo json_encode( $result );

		exit();
	}

	/**
	 * Log message to the API log file.
	 *
	 * @param string $message Log message.
	 * @param int $prio Log level.
	 */
	function logmsg( $message, $prio = LOG_DEBUG ) {
		// TODO: implement
	}
}

( new Scanner() )->validate_file();
