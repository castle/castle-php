<?php

namespace Castle;

class Failover
{
  const ALLOW = 'allow';
  const DENY = 'deny';
  const CHALLENGE = 'challenge';
  const THROW = 'throw';

  public static function strategies()
  {
    return array(self::ALLOW, self::DENY, self::CHALLENGE, self::THROW);
  }

  /**
   * Build the synthetic response returned when a request fails over (or when
   * tracking is disabled).
   *
   * @param  String|null $userId
   * @param  String      $strategy 'allow', 'deny' or 'challenge'
   * @param  String      $reason
   * @return Array
   */
  public static function prepareResponse($userId, $strategy, $reason)
  {
    return array(
      'policy' => array('action' => $strategy),
      'action' => $strategy,
      'user_id' => $userId,
      'failover' => true,
      'failover_reason' => $reason,
    );
  }
}
