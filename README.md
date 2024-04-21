Importando o pacote localmente
=====================

---
Para ajudar no desenvolvimento, você pode exigir um pacote local em um projeto local do Laravel.

Se você tem um projeto Laravel local, você pode requerer seu pacote localmente definindo um _"repositories"_ customizado <br> no composer.json arquivo de sua aplicação Laravel .

Adicione a seguinte chave de _"repositories"_ abaixo da seção "scripts" no composer.json arquivo de seu aplicativo Laravel (substitua o _"url"_ pelo diretório onde seu pacote está):

```
{
  "scripts": { ... },

  "repositories": [
    {
      "type": "path",
      "url": "../../packages/blogpackage"
    }
  ]
}
```

Agora você pode solicitar seu pacote local no aplicativo Laravel usando o namespace escolhido do pacote. Seguindo nosso exemplo, seria:


`composer require johndoe/blogpackage`

Por padrão, o pacote é adicionado à pasta vendor como um link simbólico, se possível. Se você quiser fazer uma cópia física (ou seja, espelhamento ), adicione o campo `"symlink": false` à optionspropriedade da definição do repositório :

```
{
  "scripts": { ... },

  "repositories": [
    {
      "type": "path",
      "url": "../../packages/blogpackage",
      "options": {
        "symlink": false
      }
    }
  ]
}
```

Se você tiver vários pacotes no mesmo diretório e quiser instruir o Composer a procurar por todos eles, pode listar a localização do pacote usando um caractere curinga da *seguinte maneira:

```
{
  "scripts": { ... },

  "repositories": [
    {
      "type": "path",
      "url": "../../packages/*"
    }
  ]
}
```
Importante: você precisará realizar uma atualização do compositor em seu aplicativo Laravel sempre que fizer alterações no composer.jsonarquivo de seu pacote ou em qualquer provedor que ele registrar.
