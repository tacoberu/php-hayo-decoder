php hayo decoder
================

Zdrojový kód v Hayo naparsuje a vytvoří z něho AST, které následně můžeme rovnou kompilovat do cílového jazyka.


## Fáze

1. Naparsování zdrojáku a vytvoření AST.
2. Dohledání závislostí.
3. Výsledný script obsahující všechny elementy.

Pracujeme s několika typy elementů.

- Buildin symboly = True, False, 1, 2, 3.14, "A", "č".
- Expresion
- Programové symboly - ty se musí někde dohledat.


## Popis jazyka

### Hodnoty

čísla a texty: `1`, `3.14`, `True`, `"Lorem ipsum doler ist."`

Symboly se od typů rozlišují pomocí prvního velkého písmena. Tedy:
`true = 1`
`true = True`


### Výrazy
`1 + 1`
`1 + a`
`1 + (a * a)`


### Komentáře
- řádkový `--`
- blokový `{- -}` (lze zanořovat)


### Funkce

Jedná se o lambdu přiřazenou nějakému symbolu. Strukturu určujeme buď zanořením a
odsazením, nebo explicitně složenými závorkami. Poslední prvek je výraz a výsledek je vracen.

	`fn = a b -> a + b`

	`fn = a b -> { a + b }`

	`fn = a b ->
		a + b`

	`fn = a -> { pi = 3.14; inc = a -> {base = 1; a + base}; a + b }`

	`fn = a -> {
		pi = 3.14
		inc = a -> {
			base = 1
			a + base
		}
		a + b
	}`

	`fn = a ->
		pi = 3.14
		inc = a ->
			base = 1
			a + base
		a + b`

Funkci voláme vždy s argumentem. Bez argumentu se inlinuje. Výjimka je funkce se sideeffektem, která se zpracuje speciálně.



### Streamy

	`source |> filter1 |> filter2 42 |> print`


### Flow

#### If, else, match

	`match x {
		true -> echo "True"
		_    -> echo "False"
	}`

#### Cikly

	`list.each {(x) -> echo x}`


### Typy a struktury

#### Základní typy

- čísla
- text
- funkce
- pole
- slovník



## Ke zvážení:
- makra
