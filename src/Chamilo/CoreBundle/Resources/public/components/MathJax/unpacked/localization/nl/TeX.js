/* -*- Mode: Javascript; indent-tabs-mode:nil; js-indent-level: 2 -*- */
/* vim: set ts=2 et sw=2 tw=80: */

/*************************************************************
 *
 *  MathJax/localization/nl/TeX.js
 *
 *  Copyright (c) 2009-2015 The MathJax Consortium
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 */

MathJax.Localization.addTranslation("nl", "TeX", {
  version: "2.5.0",
  isLoaded: true,
  strings: {
    ExtraOpenMissingClose: "Overtollige openende accolade of ontbrekende afsluitende accolade",
    ExtraCloseMissingOpen: "Overtollige afsluitende accolade of ontbrekende openende accolade",
    MissingLeftExtraRight: "Ontbrekende \\left of overtollige \\right",
    MissingScript: "Ontbrekend superschrift of subschrift argument",
    ExtraLeftMissingRight: "Overtollige \\left of ontbrekende \\right",
    Misplaced: "Misplaatste %1",
    MissingOpenForSub: "Ontbrekende openende accolade voor subschrift",
    MissingOpenForSup: "Ontbrekende openende accolade voor superschrift",
    AmbiguousUseOf: "Dubbelzinnig gebruik van %1",
    EnvBadEnd: "\\begin{%1} eindigde met \\end{%2}",
    EnvMissingEnd: "Ontbrekende \\end{%1}",
    MissingBoxFor: "Ontbrekende box voor %1",
    MissingCloseBrace: "Ontbrekende afsluitende accolade",
    UndefinedControlSequence: "Ongedefinieerde bewerkingsvolgorde %1",
    DoubleExponent: "Dubbele exponent: gebruik accolades om te verduidelijken",
    DoubleSubscripts: "Dubbele subschriften: gebruik accolades om te verduidelijken",
    DoubleExponentPrime: "Priem veroorzaakt een dubbele exponent: Gebruik accolades om te verduidelijken.",
    CantUseHash1: "U kunt 'macro parameter karakter #' niet gebruiken in wiskunde modus",
    MisplacedMiddle: "%1 moet zich tussen \\left en \\right bevinden",
    MisplacedLimits: "%1 is alleen toegestaan op bewerkingstekens",
    MisplacedMoveRoot: "%1 kan alleen voorkomen in een root",
    MultipleCommand: "Meerdere %1",
    IntegerArg: "Het argument voor %1 moet een geheel getal zijn",
    NotMathMLToken: "%1 is geen symbool element",
    InvalidMathMLAttr: "Ongeldig MathML attribuut: %1",
    UnknownAttrForElement: "%1 is niet een bekend attribuut voor %2",
    MaxMacroSub1: "MathJax maximaal aantal macro vervangingen overschreden; is er een recursieve macro aanroep?",
    MaxMacroSub2: "Maximaal aantal vervangingen van MathJax overschreden; is er een recursieve LaTeX-omgeving?",
    MissingArgFor: "Ontbrekend argument voor %1",
    ExtraAlignTab: "Overtollige uitlijning tab in \\cases tekst",
    BracketMustBeDimension: "Rechte haak argument voor %1 moet een dimensie zijn",
    InvalidEnv: "Ongeldige omgevingsnaam '%1'",
    UnknownEnv: "Onbekende omgeving '%1'",
    ExtraCloseLooking: "Overtollige afsluitende accolade terwijl gezocht wordt naar %1",
    MissingCloseBracket: "Kon afsluitende ']' niet vinden als argument voor %1",
    MissingOrUnrecognizedDelim: "Ontbrekend of onbekend scheidingsteken voor %1",
    MissingDimOrUnits: "Ontbrekende dimensie of zijn eenheden voor %1",
    TokenNotFoundForCommand: "Kon %1 niet vinden voor %2",
    MathNotTerminated: "Wiskunde niet afgerond in een 'text box'",
    IllegalMacroParam: "Niet toegestane macro parameter verwijzing",
    MaxBufferSize: "MathJax interne bufferomvang overschreden; is er een recursive macro aanroep?",
    CommandNotAllowedInEnv: "%1 niet toegestaan in %2 omgeving",
    MultipleLabel: "Label '%1' meerdere keren gedefinieerd",
    CommandAtTheBeginingOfLine: "%1 moet aan het begin van een regel staan",
    IllegalAlign: "Niet toegestane uitlijning gespecificeerd in %1",
    BadMathStyleFor: "Foute wiskunde stijl voor %1",
    PositiveIntegerArg: "Het argument voor %1 moet een positief geheel getal zijn",
    ErroneousNestingEq: "Foutieve nesting van vergelijking structuren",
    MultlineRowsOneCol: "De regels in de %1 omgeving moeten precies \u00E9\u00E9n kolom bevatten",
    MultipleBBoxProperty: "%1 twee keer gespecificeerd in %2",
    InvalidBBoxProperty: "'%1' lijkt niet op een kleur, een opvuldimensie of een stijl",
    ExtraEndMissingBegin: "Overtollige %1 of ontbrekende \\begingroup",
    GlobalNotFollowedBy: "%1 niet gevolgd door \\let, \\def of \\newcommand",
    UndefinedColorModel: "Kleurmodel '%1' niet gedefinieerd",
    ModelArg1: "Kleurwaardes voor het %1 model hebben 3 getallen nodig",
    InvalidDecimalNumber: "Ongeldig decimaal getal",
    ModelArg2: "Kleurwaardes voor het %1 model moeten tussen %2 en %3 liggen",
    InvalidNumber: "Ongeldig getal",
    NewextarrowArg1: "Eerst argument voor %1 moet een bewerkingsvolgorde naam zijn.",
    NewextarrowArg2: "Tweede argument voor %1 moet twee gehele getallen zijn, gescheiden door een komma",
    NewextarrowArg3: "Derde argument voor %1 moet een Unicode karakter nummer zijn",
    NoClosingChar: "Kan afsluitende %1 niet vinden",
    IllegalControlSequenceName: "Niet toegestane bewerkingsvolgorde naam voor %1",
    IllegalParamNumber: "Niet toegestane getallen of parameters gespecificeerd in %1",
    MissingCS: "%1 moet gevolgd worden door een bewerkingsvolgorde",
    CantUseHash2: "Niet toegestaan gebruik van # in een sjabloon voor %1",
    SequentialParam: "Parameters voor %1 moeten opeenvolgend genummerd zijn",
    MissingReplacementString: "Ontbrekende vervangende tekst voor definitie van %1",
    MismatchUseDef: "Gebruik van %1 stemt niet overeen met zijn definitie",
    RunawayArgument: "Ontsnapt argument voor %1?",
    NoClosingDelim: "Kan afsluitende scheidingsteken voor %1 niet vinden"
  }
});

MathJax.Ajax.loadComplete("[MathJax]/localization/nl/TeX.js");
