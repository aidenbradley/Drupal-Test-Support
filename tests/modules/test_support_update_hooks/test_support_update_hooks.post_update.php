<?php

/**
 * Sets the status of all users to 0, effectively blocked
 * Uses a batch to process the users
 */
function test_support_update_hooks_post_update_batch_block_users(array &$sandbox): void
{
    $userEntityQuery = \Drupal::entityQuery('user');

    if (isset($sandbox['total']) === false) {
        $uids = $userEntityQuery->execute();

        $sandbox['total'] = count($uids);
        $sandbox['current'] = 0;

        if ($sandbox['total'] === 0) {
            $sandbox['#finished'] = 1;

            return;
        }
    }

    $usersPerBatch = 25;

    $uids = $userEntityQuery
        ->range($sandbox['current'], $usersPerBatch)
        ->execute();

    if (count($uids) === 0) {
        $sandbox['#finished'] = 1;

        return;
    }

    foreach ($uids as $uid) {
        $user = \Drupal\user\Entity\User::load($uid);

        $user->set('status', 0);

        $user->save();

        $sandbox['current']++;
    }

    if ($sandbox['current'] >= $sandbox['total']) {
        $sandbox['#finished'] = 1;

        return;
    }

    $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);
}

/**
 * Sets the status of all users to 0, effectively blocked
 * Processes the users without batching
 */
function test_support_update_hooks_post_update_no_batch_block_users(): void
{
    $userEntityQuery = \Drupal::entityQuery('user');

    $uids = $userEntityQuery->execute();

    foreach ($uids as $uid) {
        $user = \Drupal\user\Entity\User::load($uid);

        $user->set('status', 0);

        $user->save();
    }
}
