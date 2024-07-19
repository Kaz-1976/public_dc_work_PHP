<?php

/**
 * 
 * DB接続関数
 *
 * @return mixed
 */
function db_connect()
{
    // DB接続処理
    try {
        $db = new PDO(DSN, USER, PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
        echo $e->getMessage();
        return false;
    }
    // PDOオブジェクトを返す
    return $db;
}
/**
 * 
 * SELECT文発行関数
 *
 * @return mixed
 */
function db_select($db, $sql, $bind)
{
    // 変数初期化
    $obj_data = (object) ['data' => null, 'err_flg' => false, 'err_msg' => ''];
    // SELECT発行処理
    try {
        // SELECT文準備
        $stmt = $db->prepare($sql);
        // パラメーター設定
        foreach ($bind as $item) {
            $stmt->bindValue($item['name'], $item['value']);
        }
        // SELECT文発行
        $stmt->execute();
        // データ取得
        $obj_data->data = $stmt->fetchAll();
    } catch (PDOException $e) {
        // メッセージ出力
        $obj_data->err_flg = true;
        $obj_data->err_msg = $e->getMessage();
    }
    // リターン
    return $obj_data;
}
/**
 * 
 * DML(INSERT,UPDATE,DELETE)発行関数
 *
 * @param object  $db
 * @param string  $sql
 * @param array   $bind
 * @param boolean $transaction
 * @return object
 *
 */
function db_execute($db, $sql, $bind, $transaction)
{
    // 変数初期化
    $obj_error = (object) ['err_flg' => false, 'err_msg' => ''];
    // DML発行処理
    try {
        // トランザクション発行
        if ($transaction) {
            $db->beginTransaction();
        }
        // DML文実行準備
        $stmt = $db->prepare($sql);
        // パラメーター設定
        foreach ($bind as $item) {
            $stmt->bindValue($item['name'], $item['value']);
        }
        // DML文発行
        $stmt->execute();
        // COMMIT発行
        if ($transaction) {
            $db->commit();
        }
    } catch (PDOException $e) {
        // メッセージ出力
        $obj_error->err_flg = true;
        $obj_error->err_msg = $e->getMessage();
        // ROLLBACK発行
        if ($transaction) {
            $db->rollback();
        }
    }
    // リターン
    return $obj_error;
}
