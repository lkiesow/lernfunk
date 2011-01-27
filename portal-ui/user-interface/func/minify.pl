#!/usr/bin/perl
use JavaScript::Minifier qw(minify);
open(INFILE, 'func.js') or die;
open(OUTFILE, '>func.min.js') or die;
minify(input => *INFILE, outfile => *OUTFILE);
close(INFILE);
close(OUTFILE);
