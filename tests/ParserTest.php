<?php

namespace Hazadam\Phosphor\TwigParser\Tests;

use Hazadam\Phosphor\TwigParser\ComponentTokenParser;
use Hazadam\Phosphor\TwigParser\MaskTokenParser;
use Hazadam\Phosphor\TwigParser\PropsTokenParser;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

final class ParserTest extends TestCase
{
    public function testParseCounter(): void
    {
        $this->assertSame(
            $this->counterHtml(),
            $this->twigInstance()->render('Counter.html.twig')
        );
    }

    public function testParseToDoList(): void
    {
        $this->assertSame(
            $this->toDoListHtml(),
            $this->twigInstance()->render('ToDoList.html.twig')
        );
    }

    private function twigFileLoader(): LoaderInterface
    {
        return new FilesystemLoader([
            __DIR__ . '/templates'
        ]);
    }

    private function createExtension(): ExtensionInterface
    {
        return new class () extends AbstractExtension {
            public function getTokenParsers(): array
            {
                return [
                    new ComponentTokenParser(),
                    new MaskTokenParser(),
                    new PropsTokenParser(),
                ];
            }
        };
    }

    private function counterHtml(): string
    {
        return trim('
<counter  :template="\'\u0020\u0020\u0020\u0020\u003Ch1\u003ECounter\u003C\/h1\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cp\u003E\u007B\u0020count\u0020\u007D\u003C\/p\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cp\u003E\u007B\u0020count\u0020\u007D\u003C\/p\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cp\u003E\u007B\u0020count\u0020\u007D\u003C\/p\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cp\u003E\u007B\u0020count\u0020\u007D\u003C\/p\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cp\u003E\u007B\u0020count\u0020\u007D\u003C\/p\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cdiv\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cbutton\u0020\u0040click\u003D\u0022dec\u0022\u0020type\u003D\u0022button\u0022\u003E\u002D\u003C\/button\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cbutton\u0020\u0040click\u003D\u0022inc\u0022\u0020type\u003D\u0022button\u0022\u003E\u002B\u003C\/button\u003E\n\u0020\u0020\u0020\u0020\u003C\/div\u003E\n\u0020\u0020\u0020\u0020\'">    <h1>Counter</h1>
                        <p>{ count }</p>
                    <p>{ count }</p>
                    <p>{ count }</p>
                    <p>{ count }</p>
                    <p>{ count }</p>
                <div>
        <button @click="dec" type="button">-</button>
        <button @click="inc" type="button">+</button>
    </div>
    </counter>
');
    }

    private function toDoListHtml(): string
    {
        return trim('
<to-do-list :items="[&quot;Fuse Vue and PHP&quot;,&quot;Encourage SSR&quot;]" :template="\'\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cdiv\u0020class\u003D\u0022d\u002Dflex\u0020flex\u002Dcolumn\u0020align\u002Ditems\u002Dcenter\u0022\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cul\u0020class\u003D\u0022list\u002Dgroup\u0020w\u002D50\u0022\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cli\u0020class\u003D\u0022list\u002Dgroup\u002Ditem\u0020d\u002Dflex\u0020justify\u002Dcontent\u002Dbetween\u0022\u0020v\u002Dfor\u003D\u0022\u005Bid,\u0020item\u005D\u0020in\u0020state.items\u0022\u0020\u003Akey\u003D\u0022id\u0022\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cspan\u003E\u007B\u0020item\u0020\u007D\u003C\/span\u003E\u003Cbutton\u0020\u0040click\u003D\u0022removeItem\u0028id\u0029\u0022\u0020class\u003D\u0022btn\u0020btn\u002Ddanger\u0022\u003EDelete\u003C\/button\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003C\/li\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003C\/ul\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cinput\u0020\u0040keydown.enter\u003D\u0022addItem\u0022\u0020v\u002Dmodel\u003D\u0022state.newItem\u0022\u0020type\u003D\u0022text\u0022\u0020class\u003D\u0022mt\u002D3\u0020form\u002Dcontrol\u0020w\u002D50\u0022\u0020placeholder\u003D\u0022New\u0020To\u0020Do\u0020Item\u0022\u003E\n\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u0020\u003Cbutton\u0020\u0040click\u003D\u0022addItem\u0022\u0020class\u003D\u0022w\u002D25\u0020mt\u002D3\u0020btn\u0020btn\u002Dprimary\u0022\u003EAdd\u0020item\u003C\/button\u003E\n\u0020\u0020\u0020\u0020\u003C\/div\u003E\n\'">        <div class="d-flex flex-column align-items-center">
                    <p>Loading list...</p>
                <input @keydown.enter="addItem" v-model="state.newItem" type="text" class="mt-3 form-control w-50" placeholder="New To Do Item">
        <button @click="addItem" class="w-25 mt-3 btn btn-primary">Add item</button>
    </div>
</to-do-list>
');
    }

    private function twigInstance(): Environment
    {
        $twig = new Environment($this->twigFileLoader());
        $twig->setExtensions([$this->createExtension()]);

        return $twig;
    }
}
