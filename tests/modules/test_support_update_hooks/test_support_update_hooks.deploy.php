<?php

/**
 * Sets the status of all users to 0, effectively blocked
 * Does not use a batch to process users
 */
function test_support_update_hooks_deploy_no_batch_disable_users(): void
{
    $userEntityQuery = \Drupal::entityQuery('user')->accessCheck(false);

    $uids = $userEntityQuery->execute();

    foreach ($uids as $uid) {
        $user = \Drupal\user\Entity\User::load($uid);

        if ($user === null) {
            continue;
        }

        $user->set('status', 0)->save();
    }
}

/**
 * Sets the status of all users to 0, effectively blocked
 * Uses a batch to process the users
 */
function test_support_update_hooks_deploy_with_batch_disable_users(array &$sandbox): void
{
    $userEntityQuery = \Drupal::entityQuery('user')->accessCheck(false);

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

        if ($user === null) {
            continue;
        }

        $user->set('status', 0)->save();

        $sandbox['current']++;
    }

    if ($sandbox['current'] >= $sandbox['total']) {
        $sandbox['#finished'] = 1;

        return;
    }

    $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);
}
