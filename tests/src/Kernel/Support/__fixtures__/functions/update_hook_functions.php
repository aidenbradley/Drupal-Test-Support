<?php

/*
 * sets a state value by the function name for assertions in tests
 */
function no_batch_update_hook(): void
{
    \Drupal::state()->set(__FUNCTION__, true);
}

/*
 * Sets a state value by the function name and increments its value by 1
 * for each user we're processing in the batch test
 */
function batch_update_hook(array &$sandbox): void
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

    $usersPerBatch = 5;

    $uids = $userEntityQuery
        ->range($sandbox['current'], $usersPerBatch)
        ->execute();

    if (count($uids) === 0) {
        $sandbox['#finished'] = 1;

        return;
    }

    foreach ($uids as $uid) {
        incrementUserCount(__FUNCTION__);

        $sandbox['current']++;
    }

    if ($sandbox['current'] >= $sandbox['total']) {
        $sandbox['#finished'] = 1;

        return;
    }

    $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);
}

function incrementUserCount(string $key): void
{
    $state = \Drupal::state();

    if ($state->get($key) === null) {
        $state->set($key, 0);

        return;
    }

    $state->set($key, $state->get($key) + 1);
}

/*
 * Sets a batch in the update hook but doesn't progress the value of the #finished key. e.g. 0.1 -> 0.15 -> 0.2 ---> 1
 */
function batch_update_hook_with_no_finished_progression(array &$sandbox): void
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

    $usersPerBatch = 5;

    $uids = $userEntityQuery
        ->range($sandbox['current'], $usersPerBatch)
        ->execute();

    if (count($uids) === 0) {
        $sandbox['#finished'] = 1;

        return;
    }

    foreach ($uids as $uid) {
        incrementUserCount(__FUNCTION__);

        $sandbox['current']++;
    }

    if ($sandbox['current'] >= $sandbox['total']) {
        $sandbox['#finished'] = 1;
    }
}
