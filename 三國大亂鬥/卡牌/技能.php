abstract class Special {
    // Properties
    public string $name;
    public string $name_of_thing;
    public string $name_of_special;

    // Force extending class to define this method
    abstract public function use();

    // Common method
    public function commonUse() {
        print $this->name . "的" . $this->name_of_thing . "發動技能" . $this->name_of_special;
        // Note: Recursive call to `use()` here would cause infinite recursion, so I've commented it out.
        // $this->use();
    }
}
