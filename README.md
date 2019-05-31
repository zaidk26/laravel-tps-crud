# laravel-tps-crud
Helper class to to perform CRUD operations on a Clarion Top Speed Database.
Please note you will need to purchase the softvelocity Tps ODBC Driver.
The TPS ODBC Driver crashes with multiple concurrent connection hence , this
class implement PHP flock to ensur only a single requests run at a time.

Create a system or user DSN and refernce it in env

# .env sample

ODBC_DSN="my-dsn"

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

