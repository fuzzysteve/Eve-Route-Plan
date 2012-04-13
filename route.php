<?php
error_reporting(E_ALL);

        require_once("db.inc.php");

	$from = 30000142;
	$to = 30002187;
        $plan='';
        $route=array();
		function microtime_float()
		{
			list($usec, $sec) = explode(" ", microtime());
			return ((float)$usec + (float)$sec);
		}
	
		function graph_find_path( &$G, $A, $B, $M = 50000 )
		{
		  // $P will hold the result path at the end.
		  // Remains empty if no path was found.
		  $P = array();

		  // For each Node ID create a "visit information",
		  // initially set as 0 (meaning not yet visited)
		  // as soon as we visit a node we will tag it with the "source"
		  // so we can track the path when we reach the search target

		  $V = array();

		  // We are going to keep a list of nodes that are "within reach",
		  // initially this list will only contain the start node,
		  // then gradually expand (almost like a flood fill)
		  $R = array( trim($A) );

		  $A = trim($A);
		  $B = trim($B);

		  while ( count( $R ) > 0 && $M > 0 )
		  {

			$M--;
			$X = trim(array_shift( $R ));

			foreach( $G[$X] as $Y )
			{
			  $Y = trim($Y);
			  // See if we got a solution
			  if ( $Y == $B )
			  {
				// We did? Construct a result path then
				array_push( $P, $B );
				array_push( $P, $X );
				while ( $V[$X] != $A )
				{
				   array_push( $P, trim($V[$X]) );
				   $X = $V[$X];
				}
				array_push( $P, $A );
				return array_reverse( $P );
			  }
			  // First time we visit this node?
			  if ( !array_key_exists($Y, $V) )
			  {
				// Store the path so we can track it back,
				$V[$Y] = $X;
				// and add it to the "within reach" list
				array_push( $R, $Y );
			  }
			}
		  }

		  return $P;
		}

		$time_start = microtime_float();

		$jumpArray = array();

		$query="SELECT * FROM mapSolarSystemJumpLists";
                $stmt = $dbh->prepare($query);
                $stmt->execute();

		$previousSystem = "";
		$arrayContent = "";

		while ($row=$stmt->fetchObject()) {
			$systemId = trim($row->fromSolarSystemID);
			$jumpArray[$systemId]= explode(",", strtoupper($row->toSolarSystemID));
		}


		$jumpNum = 1;

		foreach( $jumpArray[$from] as $n ) {
			if ($n == $to) {
				$jumpNum = 2;
				$route[] = "$to";
				break;
			}
		}

		if ($jumpNum == 1) {
			foreach( graph_find_path( $jumpArray, $from, $to ) as $n ) {
				if ($jumpNum > 1) {
					$route[]=  $n;
				}
				$jumpNum++;
			}
		}
		$time_end = microtime_float();
		$time = round($time_end - $time_start,5);

		echo ($jumpNum-1)."\n";
		echo $time. "\n";
		echo join(",",$route)."\n";
?>
