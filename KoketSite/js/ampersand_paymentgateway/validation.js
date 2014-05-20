// Notes:
// - Card number expressions must exactly identify a card type. ie. you cannot use the same regex for multiple cards.
// - All expressions must match the values in Ampersand_PaymentGateway_Model_Method_DirectAbstract.
Validation.creditCartTypes.set('AMPERSAND_CARTES_BANCAIRES', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_MASTERCARD_DEBIT', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_VISA_DELTA', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_VISA_ELECTRON', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_VISA_PURCHASING', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_LASER', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_DINERS_CLUB', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_VPAY', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_CARTES_BLANCHE', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_ENROUTE', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_JAL', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_DANKORT', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_CARTE_BLEUE', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_CARTA_SI', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_MAESTRO_INTERNATIONAL', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);
Validation.creditCartTypes.set('AMPERSAND_GE_MONEY_UK', [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), true]);