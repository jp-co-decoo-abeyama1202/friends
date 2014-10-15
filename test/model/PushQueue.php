<?php
require_once(__DIR__ . '/../_header.php');
//フレンド申請
$storage->PushQueue->add(21,21,\library\Model_Push::TYPE_REQUEST);
//フレンド許可
$storage->PushQueue->add(21,21,\library\Model_Push::TYPE_FRIEND);
//メッセージ
$storage->PushQueue->add(14,21,\library\Model_Push::TYPE_MESSAGE,'メッセージテスト');
//グループお誘い
$storage->PushQueue->add(21,21,\library\Model_Push::TYPE_GROUP_INVITE);
//フレンド許可
$storage->PushQueue->add(21,20,\library\Model_Push::TYPE_GROUP_MESSAGE,'グループ送信テスト');