<?php

# Enable/disable php warning messages.
ini_set('display_errors', 'On');

# ================================
# OPTIONS
# ================================

# Hide some files. (true/false)
	$hide_files = true;

# Hide these files
	$hidden = array(
		'.DS_Store',
		'localhost-styler',
		'.gitignore',
		'GruntFile.js'
	);

# Highlight special files. (true/false)
	$highlight_files = true;

# Special files/folders to be highlighted
	$highlight = array(
		'index.html',
		'index.php',
	);

# Scan sub-directories for index files. Will show an icon if index file is present in the directory.
	$detect_index_file = true;
		
# Automatically use index file if present in sub-directory. (true/false)
	$use_index_file = true;

# If these files are present in the sub-directory, localhost-styler will transfer control.
	$site_files = array(
		'index.html',
		'index.php',
		'index.htm',
		'default.htm',
	);

# localhost-styler index file name (default = index.php)
	$primary_filename = 'index.php';
	
# path of localhost-styler (assets folder) relative to primary file. (Note: assets outside localhost root folder cannot be accessed.)
	$assets_path = "";

# ================================

# Don't display ever
	$forbidden = array(
		'.',
		'..',
	);

	$server_address = $_SERVER['HTTP_HOST'];
	$doc_root = $_SERVER['DOCUMENT_ROOT'];
	if (isset($_GET['address'])) {
		$dir_rel_address = $_GET['address'];
	}
	else {
		$dir_rel_address = "";
	}
	
# IMPORTANT SECURITY CHECK. Disables access to path outside localhost root.
# Resolve path to real path.
	if ( $tmp_path = realpath($dir_rel_address) ) {
		if (strpos($tmp_path, $doc_root) !== false) {
			$path = explode('/', str_replace($doc_root.'/',"",$tmp_path));
			if ($path[0] == null) {
				$tmp_path = array_shift($path);
				$path = $tmp_path;
			}
		}
		else {
			$dir_rel_address = "";
			$path = array("");
		}
	}
	else {
		$dir_rel_address = "";
		$path = array("");
	}
	
	$is_home = $dir_rel_address ? false : true;
	$dir_full_address = $is_home ? $doc_root : $doc_root.'/'.$dir_rel_address ;
	$files = scandir($dir_full_address);
	$is_directory = false;	# if read file is or file.
	
# Remove forbidden files from $files
	$temp = array_diff($files, $forbidden);
	$files = array_diff( $temp, array('') );
	
# Remove hidden files
	if ($hide_files) {
		$temp = array_diff($files, $hidden);
		$files = array_diff( $temp, array('') );
	}
	
# Remove primary file from files list
	if ($is_home) {
		$key = array_search( $primary_filename, $files );
		if($key !== FALSE){
			unset($files[$key]);
		}
	}
	
# Sort file names naturally
	if ( $temp = natcasesort($files) )
	$file = $temp;
	
# Count items for tab navigation
	$files_count = count($files);

# Functions
	function generate_link($filename) {
		global $server_address, $dir_rel_address, $is_directory, $use_index_file, $primary_filename ;
		if ( $is_directory ) {
			if ( $use_index_file && index_file_present($filename) ) {
				return 'http://'.$server_address.'/'.($dir_rel_address ? $dir_rel_address.'/'.$filename : $filename);
			}
			else {
				return 'http://'.$server_address.'/'.$primary_filename.'?address='.($dir_rel_address ? $dir_rel_address.'/'.$filename : $filename);
			}
		}
		else {
			return 'http://'.$server_address.'/'.($dir_rel_address ? $dir_rel_address.'/'.$filename : $filename);
		}
	}// generate_link
	
	function generate_breadcrumb_path($i) {
		global $path, $server_address, $primary_filename;
		$temp_str = "";
		for ($j = 0; $j <= $i ; $j++) {
			$tmp= $temp_str.$path[$j].'/';
			$temp_str = $tmp;
		}
		return 'http://'.$server_address.'/'.$primary_filename.'?address='.rtrim($temp_str, '/');
	}// generate_breadcrumb_path
	
	function index_file_present($filename) {
		global $dir_full_address, $site_files;
		$files_here = scandir( $dir_full_address.'/'.$filename );
		
		foreach ( $site_files as $value ) {
			if ( in_array($value, $files_here) ) {
				return true;
			}
		}
		return false;
	}// index_file_present
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $dir_rel_address ? $_SERVER['SERVER_NAME'].' - '.$dir_rel_address : $_SERVER['SERVER_NAME']; ?></title>
	<link rel="shortcut icon" href="<?php echo $assets_path;?>localhost-styler/images/favicon.ico" type="image/x-icon" />

	<!-- Stylesheets -->
	<link href="<?php echo $assets_path;?>localhost-styler/stylesheets/styles.css" rel="stylesheet">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	
	<!-- Path -->
	<div class="path">
		<ol>
			<li class="home"><span class="icon icon-cloud"></span>
				<a <?php
					if ($is_home):
						echo 'class="active" tabindex="-1"';
					else:
						echo 'tabindex="'.($files_count + 3).'"';
					endif; ?>href="<?php echo "http://".$server_address; ?>">localhost</a>
				<span class="icon icon-path-arrow color-icon"></span>
			</li>
			<?php
				if (!$is_home):
					global $path;
					$count = count($path);
					for ($i=0; $i < $count; $i++): ?>
						<li class="sub-directory">
							<a <?php
								if ( $i == ($count - 1) ):
									echo 'class="active" tabindex="-1"';
								else:
									echo 'tabindex="'.($files_count + 4 + $i).'"';
								endif; ?> href="<?php echo generate_breadcrumb_path($i); ?>"><?php echo $path[$i]; ?></a>
							<span class="icon icon-path-arrow color-icon"></span>
						</li>
					<?php endfor;
				endif;
			?>
		</ol>
	</div><!-- /.path -->
	
	<!-- Files -->
	<div class="files">
		<ul >
			<li>
				<span class="icon-stack">
					<span class="icon icon-cloud color-icon"></span>
				</span>
				<a <?php
					if ($is_home):
						echo 'class="active"  tabindex="-1"';
					else:
						echo 'tabindex="1"';
					endif; ?> href="<?php echo "http://".$server_address; ?>">.</a>
			</li>
			<li>
				<span class="icon-stack">
					<span class="icon icon-directory color-icon"></span>
					<span class="icon icon-up color-icon"></span>
				</span>
				<a <?php
					if ($is_home):
						echo 'class="active"  tabindex="-1"';
					else:
						echo 'tabindex="2"';
					endif; ?> href=<?php 
					$count = count($path);
					echo generate_breadcrumb_path($count-2);
				 ?> >..</a></li>
			<?php
				if ( empty($files) ): ?>
					<li>
						<span class="icon-stack">
							<span class="icon-empty-directory color-icon" style="visibility: hidden;"></span>
						</span>
						<a class="active">Empty Directory</a>
					</li>
				<?php else:
					$no_of_files = 0;
					$no_of_directories = 0;
					if ($is_home) $tab_count = 1; else $tab_count = 3;
					foreach ( $files as $i => $v ): ?>
							<li class="<?php 
								$is_directory = is_dir($dir_full_address.'/'.$files[$i]);
								if ($is_directory) {
									$no_of_directories++;
									echo "directory";
								} else {
									$no_of_files++;
									echo "file";
								}
							?>">
								<span class="icon-stack">
									<span class="<?php
										if ($is_directory) {
											echo "icon icon-directory color-icon";
										} else {
											echo "icon icon-file color-icon";
										}
									?>"></span>
									<?php
										if ($is_directory && $detect_index_file):
											if ( index_file_present($files[$i]) ):
												if ( $use_index_file ):?>
													<span class="icon icon-index color-icon"></span>
												<?php else: ?>
													<span class="icon icon-index disabled"></span>
												<?php endif;
											endif;
										endif;
									?>
								</span>
								<a class="<?php
									if ( $highlight_files && in_array($files[$i], $highlight) ):
										echo "highlight ";
									endif; // highlight
								?>" tabindex="<?php echo $tab_count++ ?>" href="<?php
									echo generate_link($files[$i]);
								?>"><?php echo $files[$i]; ?></a>
							</li>
							
						<?php endforeach;
				endif;
			?>
		</ul>
	</div><!-- /.files -->
	
	<div class="stats">
		<ul>
			<?php if (!empty($files)): ?>
				<li><?php echo $no_of_directories; ?> <span class="stack-icon"><span class="icon icon-directory color-icon"></span></span></li>
				<li><?php echo $no_of_files; ?> <span class="stack-icon"><span class="icon icon-file color-icon"></span></span></li>
			<?php endif; ?>
		</ul>
	</div>

	<!-- jQuery -->
	<script src="<?php echo $assets_path;?>localhost-styler/scripts/jquery.1.11.1.min.js"></script>
</body>
</html>