abstract class Special {
    // Properties
    public string $name;
    public string $name_of_thing;
    public string $name_of_special;
public string $description;

    // Force extending class to define this method
    abstract protected function use();

    // Common method
    public function commonUse() {
        print $this->name . "的" . $this->name_of_thing . "發動技能" . $this->name_of_special;
        $this->use();
    }
    
    // Method to create a button
    // Method to create a button and additional text
    public function createButtonWithText() {
        echo '<div style="display: flex; align-items: center;">';
        echo '<button onclick="performAction()">' . $this->name_of_special . '</button>';
        echo '<span style="margin-left: 10px;">' . $this->description</span>';
        echo '</div>';
        echo '<script>
                function performAction() {
                    // Here you might call the PHP method via AJAX or similar
                    ' . $this->name . '().commonUse();
                }
              </script>';
    }
}
