<?php

use Drupal\user\Entity\User;

function test_support_deployhooks_deploy_only_in_deploy_php(): void
{
};

/**
 * Sets the status of all users to 0, effectively blocked
 * Does not use a batch to process users
 */
function test_support_deployhooks_deploy_no_batch_disable_users(): void
{
    foreach (\Drupal::entityQuery('user')->accessCheck(false)->execute() as $uid) {
        $user = User::load($uid);

        if ($user === null) {
            continue;
        }

        $user->set('status', 0)->save();
    }
}

/**
 * Sets the status of all users to 0, effectively blocked
 * Uses a batch to process the users
 *
 * @param array<mixed> $sandbox
 */
function test_support_deployhooks_deploy_with_batch_disable_users(array &$sandbox): void
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
        $user = User::load($uid);

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

/**
 * Sets a batch in the update hook but doesn't progress the value of the #finished key. e.g. 0.1 -> 0.15 -> 0.2 ---> 1
 *
 * @param array<mixed> $sandbox
 */
function test_support_deployhooks_deploy_with_no_finished_progression(array &$sandbox): void
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

    $usersPerBatch = 5;

    $uids = $userEntityQuery
        ->range($sandbox['current'], $usersPerBatch)
        ->execute();

    if (count($uids) === 0) {
        $sandbox['#finished'] = 1;

        return;
    }

    foreach ($uids as $uid) {
        $sandbox['current']++;
    }

    if ($sandbox['current'] >= $sandbox['total']) {
        $sandbox['#finished'] = 1;
    }
}
