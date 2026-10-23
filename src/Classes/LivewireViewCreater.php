<?php

namespace ITHilbert\Module\Classes;

class LivewireViewCreater{

    private string $model;
    private array $types;

    public function __construct(string $modelName)
    {
        $this->types = Feldart::typenFuerTabelle((new $modelName)->getTable());
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
        return match (Feldart::fuerTyp($type)) {
            Feldart::Id => $this->getInputId($name),
            Feldart::Zahl => $this->getInputNumber($name),
            Feldart::Datum => $this->getInputDate($name),
            Feldart::Datumzeit => $this->getInputDatetime($name),
            Feldart::Boolean => $this->getInputBoolean($name),
            Feldart::Euro => $this->getInputEuro($name),
            Feldart::Text => $this->getInputText($name),
        };
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
