<?php

namespace ITHilbert\Module\Classes;

use Illuminate\Support\Facades\Schema;

class LivewireClassCreater{

    private string $modul;
    private string $model;
    private string $modelNamespaces;
    private array $types;
    private string $className;

    public function __construct(string $modul, string $class, string $modelName)
    {
        $fields = Schema::getColumnListing((new $modelName)->getTable());
        $types = [];
        foreach ($fields as $field) {
            $types[$field] = Schema::getColumnType((new $modelName)->getTable(), $field);
        }
        $this->types = $types;
        $this->model = strtolower(class_basename($modelName));
        $this->modelNamespaces = $modelName;
        $this->modul = ucfirst($modul);
        $this->className = strtolower($class);
    }


    public function getClass(){
        $content = "<?php\n\n";
        //Namespaces
        $content .= 'namespace Module\\'. $this->modul ."\Livewire;\n\n";

        //Use
        $content .= 'use Livewire\Component;' . "\n";
        //$content .= 'use Illuminate\Support\Facades\Validator;' . "\n";
        $content .= 'use ' . $this->modelNamespaces . ";\n\n";

        //Class
        $content .= "class ". ucfirst($this->className). "LW extends Component\n";
        $content .= "{\n";

        //Properties
        $content .= "\tpublic ". ucfirst($this->model)." $".$this->model.";\n\n";

        //Listeners
        $content .= "\tprotected \$listeners = [''];\n\n";

        //Rules
        $content .= "\tprotected \$rules = [\n";
        $content .= $this->getRules();
        $content .= "\t];\n\n";

        //Mount
        $content .= "\tpublic function mount(". ucfirst($this->model)." \$".$this->model.")\n";
        $content .= "\t{\n";
        $content .= "\t\t\$this->".$this->model." = \$".$this->model.";\n";
        $content .= "\t}\n\n";

        //formSubmit
        $content .= "\tpublic function formSubmit()\n";
        $content .= "\t{\n";
        $content .= "\t\t\$this->validate();\n";
        $content .= "\t\t\$this->".$this->model."->save();\n";
        $content .= "\t\t\$this->emit('message', 'Erfolgreich gespeichert.');\n";
        $content .= "\t}\n\n";

        //render
        $content .= "\tpublic function render()\n";
        $content .= "\t{\n";
        $content .= "\t\treturn view('". strtolower($this->modul)."::livewire.".$this->className."', [\n";
        $content .= "\t\t]);\n";
        $content .= "\t}\n\n";

        //end
        $content .= "}\n";

        return $content;
    }

    public function getRules(){
        $rules = '';
        foreach ($this->types as $name => $type) {
            if ($name == 'id') continue;
            if ($name == 'created_at') continue;
            if ($name == 'updated_at') continue;
            if ($name == 'deleted_at') continue;

            $rules .= $this->getRule($name, $type);
        }
        return $rules;
    }

    public function getRule(string $name, string $type){
        switch ($type) {
            case 'id':
                return $this->getRuleId($name);
                break;
            case 'bigint':
                return $this->getRuleNumber($name);
                break;
            case 'integer':
                return $this->getRuleNumber($name);
                break;
            case 'string':
                return $this->getRuleText($name);
                break;
            case 'text':
                return $this->getRuleText($name);
                break;
            case 'date':
                return $this->getRuleDate($name);
                break;
            case 'datetime':
                return $this->getRuleDatetime($name);
                break;
            case 'boolean':
                return $this->getRuleBoolean($name);
                break;
            case 'decimal':
                return $this->getRuleEuro($name);
                break;
            case 'euro':
                return $this->getRuleEuro($name);
                break;
            default:
                return $this->getRuleText($name);
                break;
        }
    }

    public function getRuleId(string $name){
        return "\t\t'". $this->model.'.'.$name.'\' => \'required|integer|exists:'.$this->model.'\','."\n";
    }
    public function getRuleBoolean(string $name){
        return "\t\t'". $this->model.'.'.$name.'\' => \'required|boolean\','."\n";
    }

    public function getRuleDate(string $name){
        return "\t\t'". $this->model.'.'.$name.'\' => \'required|date\','."\n";
    }

    public function getRuleDatetime(string $name){
        return "\t\t'". $this->model.'.'.$name.'\' => \'required|date\','."\n";
    }

    public function getRuleNumber(string $name){
        return "\t\t'". $this->model.'.'.$name.'\' => \'required|numeric\','."\n";
    }

    public function getRuleEuro(string $name){
        return "\t\t'". $this->model.'.'.$name.'\' => \'required|numeric\','."\n";
    }

    public function getRuleText(string $name){
        return "\t\t'". $this->model.'.'.$name.'\' => \'required|string\','."\n";
    }
}
