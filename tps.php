<?php
namespace App\Odbc;
use Illuminate\Support\Facades\Cache;

class Tps{

  private $connection; 
  private $lf;

  /**
   * Get a collection of result of read query
   */
  public function read($query,$from=0,$take=100000){

    $this->canConnect(1);

    $this->connect(); 
    $result = collect([]);  

    try{
      $result = $this->resultToCollection(odbc_exec($this->connection,$query),$from,$take);      
    }catch(\Exception $ex){    
      throw new Exception($ex); 
    }finally{
      $this->close();
      return $result;
      
    }
  }

  /**
   * Check if connection can be made
   * Limit to 1 connections at a given time
   */
  private function canConnect($tries){  
   
    if($tries > 10){ die('Can get Access'); } 

    $this->lf = fopen('commit.lock', 'r+');
    if (!flock($this->lf, LOCK_EX)) {
      usleep(500000);
      $this->canConnect(++$tries);
    }

    return true;
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
          $arr[strtolower($fieldNames[$j])] = utf8_encode($row[$fieldNames[$j]]);
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

      $this->canConnect(1);
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
  private function connect($tries = 1){
    try{
      $this->connection = odbc_connect(env('ODBC_DSN'),'','');      
    }catch(\Exception $ex){
      if($tries > 5){ 
        flock($this->lf,LOCK_UN);
         die($ex); 
        }
      usleep(500000);
      $this->connect(++$tries);
    }     
  }


  /**
   * Close connection
   */
  private function close(){
    try{  
      odbc_close($this->connection);     
    }catch(\Exception $ex){  
      usleep(500000);  
    }finally{
       flock($this->lf,LOCK_UN);
    }    
  }

}
