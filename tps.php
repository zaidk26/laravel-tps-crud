<?php
namespace App\Odbc;

class Tps{

  private $driver;
  private $user;
  private $pass;
  private $path;
  private $connectionString;
  private $connection;
  private $dateFields = "%DATE%|%Date%|%date%";
  private $timeFields = "%TIME%|%Time%|%time%";


  /**
   * Constructor
   */
  public function __construct() {
    $this->user   = '';
    $this->driver = env('ODBC_DRIVER');
    $this->pass   = env('ODBC_PASSWORD');
    $this->path   = env('ODBC_DATABASE'); 
    $this->connectionString =  "Driver={$this->driver};Dbq={$this->path};Datefield={$this->dateFields};Timefield={$this->timeFields}";
  }

  /**
   * Get a collection of result of read query
   */
  public function read($query,$from=0,$take=100000){
    try{

      $this->connect();      
      return $this->resultToCollection(odbc_exec($this->connection,$query),$from,$take);
      
    }catch(\Exception $ex){
      throw new \Exception($ex);    
    }finally{
      $this->close();
    }
  }


  /**
   * Result to collection
   */
  public function resultToCollection($result,$from,$take){

    $numFields = odbc_num_fields($result);

    if($numFields > 0 ){

      $fieldNames = $this->getFieldNamesOfResult($result,$numFields);
      
      return $this->buildCollectionOfResult($result,$numFields,$fieldNames,$from,$take);
      
    }  

    return null; //no rows to return

  }


  /**
   * Get field names of result
   */
  private function getFieldNamesOfResult($result,$numFields){
    
    for ($i = 1;$i <= $numFields; $i++) {       
      $fieldNames[] = odbc_field_name($result, $i);
    }

    return $fieldNames;

  }

  /**
   * Iterate rows of result and build collecection array
   */
  private function buildCollectionOfResult($result,$numFields,$fieldNames,$from,$take){
    
    $collection = collect([]); 

    $i = 0;
    while ($row = odbc_fetch_array($result)) {
    
      if($i >= $from  && $i < ($from + $take)){
        
        for ($j = 0;$j < $numFields; $j++) {       
          $arr[$fieldNames[$j]] = $row[$fieldNames[$j]];
        }

        $collection->push($arr);
      }
     
      if($i >= ($from + $take)){ break; }
      $i++;
    }

    return $collection;
  }


  /**
   * Delete record 
   */
  public function delete($query){
   return $this->execQuery($query);
  }

  /**
   * Delete record 
   */
  public function update($query){
    return $this->execQuery($query);
  }

  /**
   * Delete record 
   */
  public function create($query){
   return $this->execQuery($query);
  }

  /**
   * Execute the query and return the number of rows affected
   */
  private function execQuery($query){
    try{

      $this->connect();      
      $result = odbc_exec($this->connection, $query);

      if ($result) {
        return odbc_num_rows($result);
      }
      
    }catch(\Exception $ex){
      throw new \Exception($ex);    
    }finally{
      $this->close();
    }
  }



  /**
   * Connect to DB
   */
  private function connect(){
    $this->connection = odbc_connect($this->connectionString,$this->user,$this->pass);
  }


  /**
   * Close connection
   */
  private function close(){
    try{
      odbc_close($this->connection);
    }catch(\Exception $ex){}    
  }

}
