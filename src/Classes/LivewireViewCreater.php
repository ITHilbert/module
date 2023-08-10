<?php

namespace ITHilbert\Module\Classes;

use Illuminate\Support\Facades\Schema;

class LivewireViewCreater{

    private string $model;
    private array $types;

    public function __construct(string $modelName)
    {
        $fields = Schema::getColumnListing((new $modelName)->getTable());
        $types = [];
        foreach ($fields as $field) {
            $types[$field] = Schema::getColumnType((new $modelName)->getTable(), $field);
        }
        $this->types = $types;
        $this->model = strtolower(class_basename($modelName));
    }

    public function getForm(){
        $content = "<div>\n\n";
        //Messages
        $content .= "\t@include('include.message')" . "\n\n";

        //Form öffnen
        $content .= "\t<form  wire:submit.prevent=\"formSubmit\">\n";

        foreach ($this->types as $name => $type) {
            $label = str_replace('_',' ', ucfirst($name));
            if (str_ends_with($label, ' id')) {
                $label = str_replace(' id', '', $label);
            }

            if ($name == 'id') continue;
            if ($name == 'created_at') continue;
            if ($name == 'updated_at') continue;
            if ($name == 'deleted_at') continue;

            //Kommentar
            $content .= "\t\t{{-- ".$name." --}}\n";

            //Row
            $content .= "\t\t<div class=\"".config('module.row_classes')."\">\n";

            //Col Label
            $content .= "\t\t\t<div class=\"".config('module.col_label_classes')."\">\n";

            //Label
            if(config('module.label') != ''){
                $content .= "\t\t\t\t<label for=\"{$name}\" class=\"".config('module.label')."\">{$label}</label>\n";
            }else{
                $content .= "\t\t\t\t<label for=\"{$name}\">{$label}</label>\n";
            }
            //Col Label Ende
            $content .= "\t\t\t</div>\n";

            //Col Input
            $content .= "\t\t\t<div class=\"".config('module.col_input_classes')."\">\n";

            //Input Felder
            if (str_ends_with($name, '_id')) {
                $content .= $this->getComboBox($name, $type) . "\n";
            } else {
                $content .= "\t\t\t\t". $this->getInput($name, $type);
            }
            // Col Input Ende
            $content .= "\t\t\t</div>\n";
            //Row Ende
            $content .= "\t\t</div>\n";

        }

        //Buttons
        $content .= "\t\t{{-- Buttons --}}\n";
        $content .= "\t\t<row>\n";
        $content .= "\t\t\t<col-6>\n";
        $content .= "\t\t\t\t<button-back onclick=\"window.history.back();\">Zurück</button-back>\n";
        $content .= "\t\t\t</col-6>\n";
        $content .= "\t\t\t<col-6 class=\"d-flex justify-content-end\">\n";
        $content .= "\t\t\t\t<button-save>Speichern</button-save>\n";
        $content .= "\t\t\t</col-6>\n";
        $content .= "\t\t</row>\n";

        //Form schließen
        $content .= "\t</form>\n";

        $content .= "</div>\n\n";
        return $content;
    }

    public function getComboBox(string $name, string $type){
        return <<<HTML
                        <select wire:model="{$this->model}.{$name}" @error("{$this->model}.{$name}") class="is-invalid" @enderror>
                            @foreach(config('{$this->model}.{$name}', []) as \$row)
                                @if(\$row->id !== \${$this->model}->{$name})
                                    <option value="{{\$row->id}}">{{\$row->name}}</option>
                                @else
                                    <option value="{{\$row->id}}" selected>{{\$row->name}}</option>
                                @endif
                            @endforeach
                        </select>
        HTML;
    }

    public function getInput(string $name, string $type){
        switch ($type) {
            case 'id':
                return $this->getInputId($name);
                break;
            case 'bigint':
                return $this->getInputNumber($name);
                break;
            case 'integer':
                return $this->getInputNumber($name);
                break;
            case 'string':
                return $this->getInputText($name);
                break;
            case 'text':
                return $this->getInputText($name);
                break;
            case 'date':
                return $this->getInputDate($name);
                break;
            case 'datetime':
                return $this->getInputDatetime($name);
                break;
            case 'boolean':
                return $this->getInputBoolean($name);
                break;
            case 'decimal':
                return $this->getInputEuro($name);
                break;
            case 'euro':
                return $this->getInputEuro($name);
                break;
            default:
                return $this->getInputText($name);
                break;
        }
    }

    public function getInputId(string $name){
        return '<intput-int wire:model="'.$this->model.'.'.$name.'" @error(\''.$this->model.'.'.$name.'\') class="is-invalid" @enderror /></input-int>'."\n";
    }

    public function getInputBoolean(string $name){
        return '<checkbox wire:model="'.$this->model.'.'.$name.'" @error(\''.$this->model.'.'.$name.'\') class="is-invalid" @enderror /></checkbox>'."\n";
    }

    public function getInputDate(string $name){
        return '<input-date wire:model="'.$this->model.'.'.$name.'" @error(\''.$this->model.'.'.$name.'\') class="is-invalid" @enderror /></input-date>'."\n";
    }

    public function getInputDatetime(string $name){
        return '<input-date wire:model="'.$this->model.'.'.$name.'" @error(\''.$this->model.'.'.$name.'\') class="is-invalid" @enderror /></input-date>'."\n";
    }


    public function getInputNumber(string $name){
        return '<input-number wire:model="'.$this->model.'.'.$name.'" @error(\''.$this->model.'.'.$name.'\') class="is-invalid" @enderror /></input-number>'."\n";
    }

    public function getInputEuro(string $name){
        return '<input-euro wire:model="'.$this->model.'.'.$name.'" @error(\''.$this->model.'.'.$name.'\') class="is-invalid" @enderror /></input-euro>'."\n";
    }

    public function getInputText(string $name){
        return '<input-text wire:model="'.$this->model.'.'.$name.'" @error(\''.$this->model.'.'.$name.'\') class="is-invalid" @enderror /></input-text>'."\n";
    }
}
