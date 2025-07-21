# ProfileAdv Module

This repository contains a PrestaShop module used to calculate menu recommendations for pets.

## Installation
1. Copy the `modules/profileadv` directory into the `modules/` folder of your PrestaShop installation.
2. Inside your PrestaShop back office, enable the module **ProfileAdv**.

No additional composer dependencies are required. The module relies solely on the PHP files included in this repository.

## Usage
The module calculates daily food ratios based on:
- Pet type (dog or cat)
- Age and size
- Activity level
- Physical condition

The available physical conditions are:
1. Delgado
2. Normal
3. Gordito

Daily ratio entries now also include a `menu` key with the product ID of the
recommended menu for the selected condition. These IDs are defined as constants
in the `ProfileadvMenuConstants` class.

The list of available menu constants is:

```
MENU_INICI_COCINADO
MENU_INICI_CRUDO
MENU_INICIO_CRUDO
MENU_CACHORRO_COCINADO
MENU_CACHORRO_CRUDO
MENU_ENERGY_COCINADO
MENU_ENERGY_CRUDO
MENU_OBESIDAD_COCINADO
MENU_OBESIDAD_CRUDO
MENU_SENIOR_COCINADO
MENU_SENIOR_CRUDO
MENU_ALLERGY_COCINADO
MENU_ALLERGY_CRUDO
```

Daily ratio values are defined in `modules/profileadv/pet_daily_ratios.php` and translations are stored in `modules/profileadv/translations`.

For reference examples on menu combinations, see `Menús suggerits programació.docx` (in Catalan).
