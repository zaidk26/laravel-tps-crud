# laravel-tps-crud
Helper class to to perform CRUD operations on a Clarion Top Speed Database.
Please note you will need to purchase the softvelocity Tps ODBC Driver

# .env sample

ODBC_DRIVER="{SoftVelocity Topspeed driver (*.tps)}"

ODBC_DATABASE="Z:\\Test\\CellularRepairs\\SSSCL\\"

ODBC_PASSWORD=123456

# Install

Copy class to App\Odbc folder

# Use
```
use App\Odbc\Tps;

class ApiController extends Controller
{
    public function test(Tps $tps){
        
        $jobs = $tps->read("SELECT * FROM job_control_history
                            WHERE Customer_Code='IMM011'",0,10);
                            
        //Fetchs first 10 rows as a laravel colelction

        dd($jobs);
        
        //returns the number of rows affected
        $tps->delete('Your query');
        $tps->update('Your query');
        $tps->create('Your query');

    }
}
```

