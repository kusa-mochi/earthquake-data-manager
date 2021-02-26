<?php
$dbPath = 'data.db';
$conn = new PDO('sqlite:'.$dbPath);
$query = 'select * from earthquake_data where dt >= :min_dt and dt <= :max_dt';
$min_date = $_GET['min_date'];
$max_date = $_GET['max_date'];

// GETパラメータチェック
$min_datetime = Datetime::createFromFormat('Y-m-d H:i:s', $min_date);
$max_datetime = Datetime::createFromFormat('Y-m-d H:i:s', $max_date);
$diff_datetime = $max_datetime->diff($min_datetime);
// min_dateがmax_dateよりも遅れていない場合
$invert_datetime = $diff_datetime->invert;
if($invert_datetime != 1)
{
    throw new InvalidArgumentException("min_datetime and max_datetime must be min_datetime < max_datetime");
}

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
  // エラー処理
}

while($row = $stmt->fetch(PDO::FETCH_ASSOC))
{
    echo $row['dt'].' '.$row['lat'].' '.$row['lon'].' '.$row['depth'].' '.$row['mag']."\n";
}
?>
