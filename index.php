<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(isset($_POST["submit"]))
{

    $target_dir = 'uploads/';

    $temp = explode(".", $_FILES["filepath"]["name"]);
    $newfilename = round(microtime(true)) . '.' . end($temp);

    $target_file = $target_dir.basename($newfilename);
    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

    move_uploaded_file($_FILES["filepath"]["tmp_name"], $target_file);

    require_once "Classes/PHPExcel.php";
    $tmpfname = $target_file;
    $excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
    $excelObj = $excelReader->load($tmpfname);
    $worksheet = $excelObj->getSheet(0);
    $lastRow = $worksheet->getHighestRow();

    $data = [];
    for ($row = 1; $row <= $lastRow; $row++) {
            $data[$worksheet->getCell('A'.$row)->getValue()] = $worksheet->getCell('B'.$row)->getValue();
    }
    
    $mainFile = file_get_contents('fr.json');
    $mainJson = json_decode($mainFile, true);
    foreach ($mainJson as $mainKey => $mainValue) {
        
        foreach ($mainValue as $subKey => $subVal) {

            if(isset($data[$subKey])){
                $campo = str_replace('"', '', $data[$subKey]);
                $mainJson[$mainKey][$subKey] = $campo;
                
            }
        }
    }
    $output = str_replace("\\/", "/", $mainJson);
    $jsonfilename=time().'.json';
    header('Content-disposition: attachment; filename="'.$jsonfilename.'"');
    header('Content-type: application/json');
    echo json_encode($output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>
<form enctype="multipart/form-data" method="post" role="form">
    <div class="dsp form-group">
        <label for="exampleInputFile">File Upload</label>
        <input type="file" name="filepath" id="filepath" size="150" accept=".xls,.xlsx,.ods" required>
        <p class="help-block">Only Excel/Ods File Import.</p>
    </div>
    <button type="submit" class="btn btn-default" name="submit" value="submit">Upload</button>
</form>
</body>
</html>