src_dir=/var/www/html/svenska/lists/

cnt_verbs=$(wc -l $src_dir/verb-def | cut -f1 -d' ')
echo "verb:         $cnt_verbs"
cnt_adverbs=$(wc -l $src_dir/adverb-def | cut -f1 -d' ')
echo "adverb:       $cnt_adverbs"
cnt_adjektiv=$(wc -l $src_dir/adjektiv-def | cut -f1 -d' ')
echo "adjektiv:     $cnt_adjektiv"
cnt_substantiv=$(wc -l $src_dir/substantiv-def | cut -f1 -d' ')
echo "substantiv:   $cnt_substantiv"
