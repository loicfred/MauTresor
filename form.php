<?php
require_once __DIR__ . '/assets/obj/User.php';

use assets\obj\User;

function generateForm(string $className, $object = null) {
    $reflection = new ReflectionClass($className);
    $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

    echo '<form method="post">';
    foreach ($properties as $prop) {
        $name = $prop->getName();

        // Get the type if defined
        $type = $prop->getType()?->getName() ?? 'string';

        // Determine input type
        $inputType = match($type) {
            'int', 'float', 'double' => 'number',
            'bool' => 'checkbox',
            default => 'text'
        };

        // Get current value if object provided
        $value = $object ? $object->$name : '';

        echo '<label>' . ucfirst($name) . ':</label>';
        if ($inputType === 'checkbox') {
            $checked = $value ? 'checked' : '';
            echo "<input type='checkbox' name='$name' $checked>";
        } else {
            echo "<input type='$inputType' name='$name' value='$value'>";
        }
        echo '<br>';
    }
    echo '<button type="submit">Submit</button>';
    echo '</form>';
}

// Example usage:
$user = new User(123, "Loic");
generateForm(User::class, $user);
?>