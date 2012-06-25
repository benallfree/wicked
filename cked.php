<?

require_once('classes/Wicked.class.php');


Wicked::init(array(
  'mixins'=>array(
    'FilterEvent',
    'ActionEvent',
  )
));
