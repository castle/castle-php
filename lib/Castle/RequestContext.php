<?php

class Castle_RequestContext
{
  # Extract a request context from the $Server environment.
  public static function extract() {
    return array(
      'library' => array(
        'name' => 'castle-php',
        'version' => Castle::VERSION
      )
    );
  }

  # Extract a request context from the $Server environment as JSON.
  public static function extractJSON() {
    return json_encode(self::extract());
  }
}
