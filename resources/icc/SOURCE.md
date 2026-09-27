# sRGB colour profile

`sRGB-v2-micro.icc` - a compact ICC v2 profile of the sRGB colour space (456 bytes, 42-point tone curve), taken
unchanged from https://github.com/saucecontrol/Compact-ICC-Profiles (file `profiles/sRGB-v2-micro.icc`, commit
`8e22c542d632e8800625581c4b417a2d081c02b4` of 2019-08-20, sha256
`0a8a33aea66a6f154a5642ebe168ef287e73265d9f7b51c42a45e6eedbacda7a`). Rights: Creative Commons Zero v1.0 Universal
(`LICENSE`) - dedicated to the public domain.

The generator embeds it as the output intent of a ZUGFeRD / Factur-X PDF: PDF/A asks for a device independent colour
space, and the page contents of the rendered invoice use DeviceRGB.
