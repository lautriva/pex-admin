function animate_obfuscated_text(text)
{
  var alphabet =
  [
    /* 0 px */ "",
    /* 1 px */ "i,;.:!|î",
    /* 2 px */ "l'`Ììí·´",
    /* 3 px */ "It[]ÍÎÏïªº•°",
    /* 4 px */ " kf(){}*¤²”\"", //§
    /* 5 px */ "ABCDEFGHJKLMNOPQRSTUVWXYZabcdeghjmnopqrsuvwxyz" +
               "/?$%&+-#_¯=^¨£ÀÁÂÃÄÅÇÈÉÊËÑÒÓÔÕÖÙÚÛÜÝ" +
               "àáâãäåçèéêëñðòóôõöùúûüýÿ0123456789Ææß×¼½¿¬«»",
    /* 6 px */ "~@®÷±",
    /* 7 px */ "µµµµµ",
  ];

  var new_text = "";
  for (var i = 0; i < text.length; i++)
  {
    var new_char = text[i];

    var len = -1;
    var pos = -1;
    //find char lenght & pos
    for (var j = 1; j < alphabet.length; j++)
    {
      for (var k = 0; k < alphabet[j].length; k++)
      {
        if (alphabet[j][k] == new_char)
        {
          len = j;
          pos = k;
          break;
        }
      }
      if (len >= 0)
        break;
    }

    if (len >= 0)
    {
      var new_pos;
      do
      {
        new_pos = Math.floor(Math.random() * alphabet[len].length);
      }
      while (new_pos == pos);

      new_text += alphabet[len][new_pos];
    }
    else
      new_text += new_char;
  }
  return new_text;
}

function animate_obfuscated_tree(node)
{
  var split = $(node).contents();
  for (var i = 0; i < split.length; i++)
  {
    var subnode = split[i];
    if (subnode.nodeType !== 3)
      animate_obfuscated_tree(subnode);
    else
      subnode.textContent = animate_obfuscated_text(subnode.textContent);
  }
}

function animate_obfuscated_html()
{
  var obfuscated_texts = $('.obfuscated');
  for (var j = 0; j < obfuscated_texts.length; j++)
  {
    var obf = obfuscated_texts[j];
    animate_obfuscated_tree(obf);
  }
}

setInterval(animate_obfuscated_html, 25);