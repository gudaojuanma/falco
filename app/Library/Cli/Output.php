<?php

namespace App\Library\Cli;

use Phalcon\Text;

class Output
{
    const NC = "\e[0m";

    const GREY = "\e[1;30m";
    const RED = "\e[1;31m";
    const GREEN = "\e[1;32m";
    const YELLOW = "\e[1;33m";
    const PURPLE = "\e[1;34m";
    const PINK = "\e[1;35m";
    const CYAN = "\e[1;36m";
    const WHITE = "\e[1;37m";

    const BOLD = "\e[1;1m";
    const LIGHT = "\e[1;2m";
    const ITALIC = "\e[1;3m";
    const UNDERLINE = "\e[1;4m";
    const BG_GREY = "\e[1;5m";
    // const  = "\e[1;6m";
    const FILL = "\e[1;7m";

    public function format($content, $style)
    {
        return $style . $content . (Text::endsWith($content, self::NC) ? '' : self::NC);
    }

    public function green($content)
    {
        return $this->format($content, self::GREEN);
    }

    public function red($content)
    {
        return $this->format($content, self::RED);
    }

    public function yellow($content)
    {
        return $this->format($content, self::YELLOW);
    }

    public function writeln($message = '', ...$arguments)
    {
        if (count($arguments)) {
            call_user_func('printf', $message, ...$arguments);
        } else {
            echo $message;
        }
        echo PHP_EOL;
    }

    public function info($message, ...$parameters)
    {
        $this->writeln($this->format($message, self::GREEN), ...$parameters);
    }

    public function comment($message, ...$parameters)
    {
        $this->writeln($this->format($message, self::YELLOW), ...$parameters);
    }

    public function question($message, ...$parameters)
    {
        $this->writeln($this->format($message, self::CYAN), ...$parameters);
    }

    public function error($message, ...$parameters)
    {
        $this->writeln($this->format($message, self::RED), ...$parameters);
    }

    public function table($data)
    {
        $thead = array_shift($data);

        $widths = [];
        $srcTemplate = [''];
        foreach ($thead as $index => $field) {
            $srcTemplate[] = ' %%-%ds ';
            $widths[$index] = strlen($field);
        }
        $srcTemplate[] = PHP_EOL;
        $templateTemplate = implode('|', $srcTemplate);

        foreach ($data as $row) {
            foreach ($row as $index => $value) {
                $widths[$index] = max(strlen($value), $widths[$index]);
            }
        }

        $line = $this->horizontalBorder($widths);
        array_unshift($widths, $templateTemplate);
        $template = call_user_func_array('sprintf', $widths);

        echo $line;
        $this->thead($thead, $widths);
        echo $line;
        foreach ($data as $index => $row) {
            array_unshift($row, $template);
            call_user_func_array('printf', $row);
            echo $line;
        }
    }

    protected function thead($thead, $widths)
    {
        foreach ($thead as $index => $field) {
            $thead[$index] = $this->format($field, self::GREEN);
            $widths[$index + 1] += strlen(self::GREEN) + strlen(self::NC);
        }
        $template = call_user_func_array('sprintf', $widths);
        array_unshift($thead, $template);
        call_user_func_array('printf', $thead);
    }

    protected function horizontalBorder($widths)
    {
        $line = '+';
        foreach ($widths as $width) {
            $line .= str_repeat('-', $width + 2) . '+';
        }
        return $line . PHP_EOL;
    }

}
