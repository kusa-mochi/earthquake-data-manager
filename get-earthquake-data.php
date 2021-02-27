<?php
$dbPath = 'data.db';
$conn = new PDO('sqlite:'.$dbPath);
$query = 'select * from earthquake_data where dt >= :min_dt and dt <= :max_dt';
$min_date = $_GET['min_date'];
$max_date = $_GET['max_date'];

///// ▼GETパラメータチェック▼ /////
// GETパラメータが渡されていない場合
if(empty($min_date) || empty($max_date))
{
  throw new InvalidArgumentException("min_date and max_date must not be empty.");
}
// GETパラメータに渡された文字列のフォーマットが不正な場合
$min_datetime = Datetime::createFromFormat('Y-m-d H:i:s', $min_date);
$max_datetime = Datetime::createFromFormat('Y-m-d H:i:s', $max_date);
if($min_datetime == false || $max_datetime == false)
{
  throw new InvalidArgumentException("The string in min_date and max_date must follow the datetime format 'Y-m-d H:i:s'.");
}
// GETパラメータに渡された年月日・時刻が不正な場合
$valid_max_diff = "+1 day"; // min_dateとmax_dateに許容する最大日時差
$tmp_min_datetime = Datetime::createFromFormat('Y-m-d H:i:s', $min_date);
$tmp_min_datetime->modify($valid_max_diff);
if($tmp_min_datetime < $max_datetime)
{
  throw new InvalidArgumentException("Max diff between min_date and max_date is ".$valid_max_diff);
}
// min_dateがmax_dateよりも遅れていない場合
$diff_datetime = $max_datetime->diff($min_datetime);
$invert_datetime = $diff_datetime->invert;
if($invert_datetime != 1)
{
    throw new InvalidArgumentException("min_date and max_date must be min_date < max_date.");
}
///// ▲GETパラメータチェック▲ /////

try
{
  // プレースホルダ付のSQLクエリの処理を準備する。
  $stmt = $conn->prepare($query);
  // プレースホルダに値をセットして、クエリの処理を実行する。
  $stmt->execute(array(
    'min_dt' => $min_date,
    'max_dt' => $max_date
  ));
}
catch(PDOException $e)
{
  throw new Exception();
}

$ary = array();
while($row = $stmt->fetch(PDO::FETCH_ASSOC))
{
  $ary[] = array(
    'datetime' => $row['dt'],
    'latitude' => floatval($row['lat']),
    'longitude' => floatval($row['lon']),
    'depth' => floatval($row['depth']),
    'magnitude' => floatval($row['mag'])
  );
}
echo json_encode($ary);
?>
