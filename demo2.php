<?php
	#functions returns all possible combinations of a different classes
	function multiply($course_1, $course_combos, $limit, $zones) {
    	$combined = array();
    	$new_combos = array();
		$count = 0;
		#if 2-D array course_combos is empty fill it up with the
		#array course_1 making each element an array of its own
    	if (empty($course_combos)) {
     	   for ($i = 0; $i < count($course_1); $i=$i+1) {
			 if (cmp_days($limit, $course_1[$i]->days) == false)
				if (time_zone_overlap($course_1[$i], $zones) == false)
    	        	array_push($new_combos, array($course_1[$i]));
    	   }
			$count = 1;
		}
		else {
        	for ($i = 0; $i < count($course_combos); $i=$i+1) {
            	for ($j = 0; $j < count($course_1); $j=$j+1) {
					#If there is no overlap between elements in the array
					#and the new element push it into the array
                	if (overlap($course_combos[$i], $course_1[$j]) == false){
						if (cmp_days($limit, $course_1[$j]->days) == false) {
							if(time_zone_overlap($course_1[$j],$zones)==false){
                    			array_push($new_combos, $course_combos[$i]);
                    			array_push($new_combos[$count], $course_1[$j]);
                    			$count = $count + 1;
							}
						}
                	}
					else
						echo "OVERLAP!\n";
    	        }
			}
		}
		if ($count != 0)
			return $new_combos;
		else
			return $course_combos;
	}

	function time_zone_overlap($course, $zone) {
		if ($course->times == "")
			return false;
		$start1 = convert(split(" - ", $course->times)[0]);
		$end1 = convert(split(" - ", $course->times)[1]);
		if ($zone == "M") {
			$start2 = 1200;
			$end2 = 2400;
			return time_overlap($start1, $end1, $start2, $end2);
		}
		else if ($zone == "AN") {
			$start2 = 800;
			$end2 = 1150;
			return time_overlap($start1, $end1, $start2, $end2);
		}
		else if ($zone == "N") {
			$start2 = 800;
			$end2 = 1750;
			return time_overlap($start1, $end1, $start2, $end2);
		}
		else if ($zone == "MN") {
			$start2 = 1200;
			$end2 = 1750;
			return time_overlap($start1, $end1, $start2, $end2);
		}
		else if ($zone == "MA") {
			$start2 = 1800;
			$end2 = 2400;
			return time_overlap($start1, $end1, $start2, $end2);
		}
		else if ($zone == "A") {
			$start2 = 800;
			$end2 = 1150;
			$start3 = 1800;
			$end3 = 2400;
			if (time_overlap($start1, $end1, $start2, $end2) == false) {
				if ((time_overlap($start1, $end1, $start3, $end3) == false))
					return false;
				else
					return true;
			}
			else
				return true;
		}
		else
			return false;
	}

	#converts time in military time
    function convert($time12hour) {
        $tail = substr($time12hour, -2);
        $time12hour = substr($time12hour, 0, count($time12hour) - 3);
        $minutes = intval(split(":", $time12hour)[1]);
        $hour = 100*intval(split(":", $time12hour)[0]);
        if (($tail == "am"&&$hour != 1200)||($tail == "pm"&&$hour == 1200))
            $time = $hour+$minutes;
        else
            $time = $hour+$minutes+1200;
        return $time;
    }

	#compares days to see if there are any overlapping days
    function cmp_days($str1, $str2) {
		if ($str1 == "TBD" || $str2 == "TBD")
			return false;
        $len1 = strlen( $str1 );
        $len2 = strlen( $str2 );
        for ($i = 0; $i < $len1; $i = $i + 1) {
            for ($j = 0; $j < $len2; $j = $j + 1) {
                if ($str1[$i] == $str2[$j])
                    return true;
            }
        }
        return false;
    }

	#checks if the times in military overlap
    function time_overlap($start1, $end1, $start2, $end2) {
        if ($start1 == $start2 || $end1 == $end2)
            return true;
        else if (($start1 < $start2 && $start2 < $end1) || ($start1 < $end2
&& $end2 < $end1))
            return true;
        else if (($start2 < $start1 && $start1 < $end2) || ($start2 < $end1
&& $end1 < $end2))
            return true;
        else if (($start1 < $start2 && $end2 < $end1) || ($start2 < $start1
&& $end1 < $end2))
            return true;
        else
            return false;
    }

	#checks if an array of classes, and the new class that is about to be
	#added in overlap at all
	function overlap($courses, $course_temp) {
		for ($i = 0; $i < count($courses); $i = $i + 1) {
			if (overlap_courses($courses[$i], $course_temp) == true) {
				return true;
			}
		}
		return false;
	}

	#calls functions to see if two courses overlap
    function overlap_courses($course1, $course2) {
        if (cmp_days($course1->days, $course2->days) == false)
            return false;
        else {
            $start1 = convert(split(" - ", $course1->times)[0]);
            $start2 = convert(split(" - ", $course2->times)[0]);
            $end1 = convert(split(" - ", $course1->times)[1]);
            $end2 = convert(split(" - ", $course2->times)[1]);
            return time_overlap($start1, $end1, $start2, $end2);
        }
    }

	// our function to read from the command line
	function read_stdin()
	{
		$fr=fopen("php://stdin","r");   // open our file pointer to read
		$input = fgets($fr,128);        // read a maximum of 128 characters
		$input = rtrim($input);         // trim any trailing spaces.
		fclose ($fr);                   // close the file handle
		return $input;                  // return the text entered
	}

	#class type course
    class course {
        var $name;
        var $days;
        var $times;
		var $CRN;
        public function __construct($name, $days, $times, $CRN) {
            $this->name = $name;
            $this->days = $days;
            $this->times = $times;
			$this->CRN = $CRN;
        }
        public function print_course() {
            echo "Name:$this->name\n";
            echo "Days:$this->days\n";
            echo "Times:$this->times\n";
			echo "CRN:$this->CRN\n";
			echo "------------------------------\n";
        }
    }

	#create classes
    $ECE_lecture1 = new course("ECE 200 Lecture", "TR", "8:00 am - 8:50
am", 1120);
    $ECE_lecture2= new course("ECE 200 Lecture", "TR", "10:00 am - 10:50
am", 69);
	$ECE_lecture3 = new course("ECE 200 Lecture", "TR", "11:00 am - 11:50
am", 34);

    $ECE_lab1= new course("ECE 200 Lab", "W", "01:00 pm - 2:50 pm", 3839);
	$ECE_lab2 = new course("ECE 200 Lab", "F", "1:00 pm - 2:50 pm", 34332);

	$FRENCH1011 = new course("FRENCH 101", "MWR", "9:00 am - 10:50 am", 3454);
	$FRENCH1012 = new course("FRENCH 101", "TWF", "3:00 pm - 4:20 pm", 8303);
	$FRENCH1013 = new course("FRENCH 101", "R", "5:00 pm - 7:50 pm", 609);

	$CS2751 = new course("CS 275", "TR", "11:00 am - 12:20 pm", 6023);
	$CS2752 = new course("CS 275", "TR", "12:30 pm - 1:50 pm", 7421);

	$MATH2211 = new course("MATH 221", "MWF", "1:00 pm - 2:50 pm", 6970);
	$MATH2212 = new course("MATH 221", "TR", "6:00 pm - 7:50 pm", 3242);
	$MATH2213 = new course("MATH 221", "MW", "6:00 pm - 7:50 pm", 5644);
	$MATH2214 = new course("MATH 221", "MWF", "9:00 am - 9:50 am", 6578);

	$CS2601 = new course("CS 260", "R","11:00 am - 1:50 pm", 325);
	$CS2602 = new course("CS 260", "M", "2:00 pm - 4:50 pm", 5765);
    $CS2603 = new course("CS 260", "TBD", "", 83234);

	#Create an array for each different class
	$list1 = array($ECE_lecture1, $ECE_lecture2, $ECE_lecture3);
	$list2 = array($ECE_lab1, $ECE_lab2);
	$list3 = array($FRENCH1011, $FRENCH1012, $FRENCH1013);
	$list4 = array($CS2751, $CS2752);
	$list5 = array($MATH2211, $MATH2213,$MATH2213, $MATH2214);
	$list6 = array($CS2601, $CS2602, $CS2603);

	#create a 2-D array of the arrays of each class
	$list = array($list1, $list2, $list3, $list4, $list5, $list6);

	#initialize the array which will contain arrays of possible class
	#combinations
	$list_combos = array();

	// show them a message to enter their name
	echo "What days you want to limit\n";
	echo "ex MRF = Mondays, Thursdays, and Fridays\n";
	echo ">";

	// the script will wait here until the user has entered something and hit
	// ENTER
	$limit = read_stdin();
	echo "Choose what timezones to have classes in ex (MA = Mornings and Afternoons)\n";
	echo "If it doesn't matter leave it blank\n";
	$zones = read_stdin();
	#For loop to decipher the combinations
	for ($i = 0; $i < count($list); $i = $i + 1)
		$list_combos = multiply($list[$i], $list_combos, $limit, $zones);

	#print the list combinations
	echo count($list_combos);
	echo " possible schedules\n\n";
	echo "==========================\n";
	for ($i = 0; $i < count($list_combos); $i=$i+1) {
		$num= $i+1;
		echo "\nLECTURE $num\n";
		echo "==========================\n";
		for ($j = 0; $j < count($list_combos[$i]); $j=$j+1) {
			$list_combos[$i][$j]->print_course();
		}
	}
?>
