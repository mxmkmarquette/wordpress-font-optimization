# Web Font Optimization Documentation
 
This documentation belongs to the WordPress plugin [Web Font Optimization](https://wordpress.org/plugins/web-font-optimization/).

**The plugin is in beta. Please submit your feedback on the [Github forum](https://github.com/o10n-x/font-optimization/issues).**

The plugin provides an advanced management solution for the following font loading technologies:

* [Font Face API](https://developer.mozilla.org/nl/docs/Web/API/FontFace)
* [Font Face Observer](https://fontfaceobserver.com/)
* [Google Font Loader](https://developers.google.com/fonts/docs/webfont_loader)

The plugin contains many unique innovations such as async and timed font loading and/or rendering which enables to load and/or render fonts only on specific screen sizes/devices using a [Media Query](https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries), when an element scrolls into view or using methods for page load time optimization purposes (`requestAnimationFrame` with frame targeting and more). Timed font loading is available for all loading strategies. 

The plugin contains a tool to download and install Google fonts locally, it provides an option to push fonts using HTTP/2 Server Push, it enables to remove linked fonts from HTML and CSS source code (`<link rel="stylesheet">` and `@import` links) and to remove Google Font Loader from HTML and javascript source code.

Additional features can be requested on the [Github forum](https://github.com/o10n-x/font-optimization/issues).

## Getting started

Before you start using the plugin it is important to determine a font loading strategy for your website. The article about [Web Font Optimization](https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/webfont-optimization) by Google provides a good start point. For more advanced insights you can read the article [Load Web Fonts Progressively](https://www.filamentgroup.com/lab/font-events.html) by Filmentgroup.

One of the primary issues of font loading is called FOUT, FOIT or FOFT. Flash of Unstyled Text, Flash of Invisible Text and Flash of Faux Text. More information about the issue is available on [CSS Tricks](https://css-tricks.com/fout-foit-foft/).

The Web Font Optimization plugin enables to achieve a instant styled font display and it enables to set classes on any element before and after a font is loaded. The plugin also enables to define a custom method to call when a font is loaded containing information about the font (e.g. the Font Face API object of the individual font). This enables to achieve the best font loading result possible with simple JSON based configuration.

Table of contents
=================

<!--ts-->
   * [Font Face API Configuration](#font-face-api-configuration)
   * [Font Face Observer Configuration](#font-face-observer-configuration)
   * [Google Font Loader Configuration](#google-font-loader-configuration)
<!--te-->

# Font Face API Configuration

Font Face API is supported by modern versions of Chrome and Firefox. The Font Face API configuration of the plugin is based on a [JSON schema](https://pagespeed.pro/schemas/fonts.json) which can be easily edited using the provided JSON editor in the plugin.

[[https://github.com/o10n-x/font-optimization/blob/master/docs/images/font-face-api-json-editor.png|alt=Font Face API Configuration]]

The configuration is a array of JSON objects. Each object is a group of fonts to load via the Font Face API. The group can contain it's own before and onload actions, enable/disable settings such as `rel="preload"` for WOFF2 fonts and configure group specific load and render timing. 

The `families` property is an array of JSON objects for individual fonts to load. Each font object should contain at least the properties `family` (the name of the font family) and `src` (the font source URI's). `src` can be a string or an object with the properties `woff` and `woff2`. The optional `options` property is an object that can contain the properties `weight`, `style`, `stretch`, `variant`, `unicodeRange` and `featureSettings`. These options are directly passed to the Font Face API.

The `beforeload` and `onload` properties are an object that can define actions before and after loading a font. The object supports two properties: `classList` and `method`. 

The `method` property enables to call a javascript function before or after a font is loaded. The method receives the Font Face API object as it's argument.

The `classList` property is an object containing the properties `add` and `remove` which can be a string or an array of strings containing the class names to add or remove. An optional `target` property enables to define a element target on which to add or remove the class(es). Without an `target` property the classes are added to the `documentElement` (`<html>`).

#### Example Configuration

```json
[
  {
    "families": [
      {
        "family": "Roboto",
        "src": {
          "woff2": "/wp-content/themes/twentysixteen/fonts/roboto-v18-latin-regular.woff2",
          "woff": "/wp-content/themes/twentysixteen/fonts/roboto-v18-latin-regular.woff"
        },
        "options": {
          "weight": 400,
          "style": "normal"
        }
      }
    ],
    "beforeload": {
      "classList": {
        "add": "roboto-loading",
        "target": ".special-text"
      }
    },
    "onload": {
      "classList": {
        "remove": "roboto-loading",
        "target": ".special-text"
      }
    },
    "rel_preload": true,
    "load_position": "timing",
    "load_timing": {
    	"type": "inview",
    	"media": ".special-text"
	}
  }
]
```

<details/>
  <summary>JSON schema for Font Face API config</summary>

```json
{
    "title": "Font Face API configuration",
    "type": "array",
    "items": {
        "title": "Font load config",
        "type": "object",
        "properties": {
            "families": {
                "title": "Fonts to load",
                "type": "array",
                "items": {
                    "title": "Font family",
                    "type": "object",
                    "properties": {
                        "family": {
                            "title": "Font family name",
                            "type": "string",
                            "minLength": 1
                        },
                        "src": {
                            "oneOf": [{
                                "title": "Font source URI",
                                "type": "string",
                                "format": "uri",
                                "minLength": 1
                            }, {
                                "title": "Multiple sources",
                                "type": "object",
                                "properties": {
                                    "woff2": {
                                        "type": "string",
                                        "format": "uri",
                                        "minLength": 1
                                    },
                                    "woff": {
                                        "type": "string",
                                        "format": "uri",
                                        "minLength": 1
                                    }
                                },
                                "additionalProperties": false
                            }]
                        },
                        "options": {
                            "title": "Web Font Observer options",
                            "type": "object",
                            "properties": {
                                "weight": {
						            "oneOf": [{
						                "type": "string",
						                "enum": ["normal", "bold", "bolder", "lighter", "initial", "inherit"]
						            }, {
						                "type": "number",
						                "enum": [100, 200, 300, 400, 500, 600, 700, 800, 900]
						            }]
						        },
                                "style": {
						            "type": "string",
						            "enum": ["normal", "italic", "oblique", "initial", "inherit"]
						        },
                                "stretch": {
						            "type": "string",
						            "enum": ["ultra-condensed", "extra-condensed", "condensed", "semi-condensed", "normal", "semi-expanded", "expanded", "extra-expanded", "ultra-expanded", "initial", "inherit"]
						        },
                                "variant": {
						            "type": "string",
						            "enum": ["normal", "small-caps", "initial", "inherit"]
						        },
                                "unicodeRange": {
                                    "type": "string"
                                },
                                "featureSettings": {
                                    "type": "string"
                                }
                            },
                            "additionalProperties": false
                        }
                    },
                    "required": ["family", "src"],
                    "additionalProperties": false
                },
                "uniqueItems": true
            },
            "beforeload": {
                "title": "Before font load actions",
                "type": "object",
                "properties": {
                    "classList": {
			            "title": "Classlist modifications",
			            "type": "object",
			            "properties": {
			                "add": {
			                    "title": "Class name(s) to add",
			                    "oneOf": [{
			                        "type": "string",
			                        "minLength": 1
			                    }, {
			                        "type": "array",
			                        "items": {
			                            "type": "string",
			                            "minLength": 1
			                        },
			                        "uniqueItems": true
			                    }]
			                },
			                "remove": {
			                    "title": "Class name(s) to remove",
			                    "oneOf": [{
			                        "type": "string",
			                        "minLength": 1
			                    }, {
			                        "type": "array",
			                        "items": {
			                            "type": "string",
			                            "minLength": 1
			                        },
			                        "uniqueItems": true
			                    }]
			                },
			                "target": {
			                    "title": "QuerySelector for class modification",
			                    "type": "string"
			                }
			            },
			            "additionalProperties": false
			        },
                    "method": {
                        "title": "Javascript method to call",
                        "type": "string"
                    }
                },
                "additionalProperties": false
            },
            "onload": {
                "title": "After font load actions",
                "type": "object",
                "properties": {
                    "classList": {
			            "title": "Classlist modifications",
			            "type": "object",
			            "properties": {
			                "add": {
			                    "title": "Class name(s) to add",
			                    "oneOf": [{
			                        "type": "string",
			                        "minLength": 1
			                    }, {
			                        "type": "array",
			                        "items": {
			                            "type": "string",
			                            "minLength": 1
			                        },
			                        "uniqueItems": true
			                    }]
			                },
			                "remove": {
			                    "title": "Class name(s) to remove",
			                    "oneOf": [{
			                        "type": "string",
			                        "minLength": 1
			                    }, {
			                        "type": "array",
			                        "items": {
			                            "type": "string",
			                            "minLength": 1
			                        },
			                        "uniqueItems": true
			                    }]
			                },
			                "target": {
			                    "title": "QuerySelector for class modification",
			                    "type": "string"
			                }
			            },
			            "additionalProperties": false
			        },
                    "method": {
                        "title": "Javascript method to call",
                        "type": "string"
                    }
                },
                "additionalProperties": false
            },
            "rel_preload": {
                "title": "Enable Preload API font loader for font group",
                "type": "boolean",
                "default": false
            },
            "requestAnimationFrame": {
                "title": "Render font group using requestAnimationFrame",
                "type": "boolean",
                "default": false
            },
            "load_position": {
                "title": "Load position of web fonts",
                "type": "string",
                "enum": ["header", "timing"],
                "default": "header"
            },
            "load_timing": {
	            "title": "Timing configuration",
	            "oneOf": [{
	                "type": "object",
	                "properties": {
	                    "type": {
	                        "title": "Timing method",
	                        "type": "string",
	                        "enum": [
	                            "domReady"
	                        ],
	                        "default": "domReady"
	                    }
	                },
	                "required": ["type"]
	            }, {
	                "type": "object",
	                "properties": {
	                    "type": {
	                        "title": "Timing method",
	                        "type": "string",
	                        "enum": [
	                            "requestAnimationFrame"
	                        ],
	                        "default": "requestAnimationFrame"
	                    },
	                    "frame": {
	                        "title": "Frame number to start script execution.",
	                        "oneOf": [{
	                            "type": "string",
	                            "enum": [""]
	                        }, {
	                            "type": "number",
	                            "minimum": 1,
	                            "default": 1
	                        }]
	                    }
	                },
	                "required": ["type"]
	            }, {
	                "type": "object",
	                "properties": {
	                    "type": {
	                        "title": "Timing method",
	                        "type": "string",
	                        "enum": [
	                            "inview"
	                        ],
	                        "default": "inview"
	                    },
	                    "selector": {
	                        "title": "CSS selector",
	                        "type": "string",
	                        "minLength": 1
	                    },
	                    "offset": {
	                        "title": "Offset in pixels from the edge of the element.",
	                        "type": "number"
	                    }
	                },
	                "required": ["type", "selector"]
	            }, {
	                "type": "object",
	                "properties": {
	                    "type": {
	                        "title": "Timing method",
	                        "type": "string",
	                        "enum": [
	                            "media"
	                        ],
	                        "default": "media"
	                    },
	                    "media": {
	                        "title": "Media query",
	                        "type": "string",
	                        "minLength": 1
	                    }
	                },
	                "required": ["type", "media"]
	            }]
	        },
            "render_timing": {
	            "title": "Timed font render",
	            "oneOf": [{
	                "type": "object",
	                "properties": {
	                    "enabled": {
	                        "title": "Timed font render",
	                        "type": "boolean",
	                        "enum": [false]
	                    },
	                    "type": {},
	                    "frame": {},
	                    "timeout": {},
	                    "setTimeout": {},
	                    "selector": {},
	                    "offset": {},
	                    "media": {}
	                },
	                "required": ["enabled"],
	                "additionalProperties": false
	            }, {
	                "allOf": [{
	                    "type": "object",
	                    "properties": {
	                        "enabled": {
	                            "title": "Timed font render",
	                            "type": "boolean",
	                            "enum": [true]
	                        },
	                        "type": {},
	                        "frame": {},
	                        "timeout": {},
	                        "setTimeout": {},
	                        "selector": {},
	                        "offset": {},
	                        "media": {}
	                    },
	                    "required": ["enabled", "type"],
	                    "additionalProperties": false
	                }, {
			            "title": "Timing configuration",
			            "oneOf": [{
			                "type": "object",
			                "properties": {
			                    "type": {
			                        "title": "Timing method",
			                        "type": "string",
			                        "enum": [
			                            "domReady"
			                        ],
			                        "default": "domReady"
			                    }
			                },
			                "required": ["type"]
			            }, {
			                "type": "object",
			                "properties": {
			                    "type": {
			                        "title": "Timing method",
			                        "type": "string",
			                        "enum": [
			                            "requestAnimationFrame"
			                        ],
			                        "default": "requestAnimationFrame"
			                    },
			                    "frame": {
			                        "title": "Frame number to start script execution.",
			                        "oneOf": [{
			                            "type": "string",
			                            "enum": [""]
			                        }, {
			                            "type": "number",
			                            "minimum": 1,
			                            "default": 1
			                        }]
			                    }
			                },
			                "required": ["type"]
			            }, {
			                "type": "object",
			                "properties": {
			                    "type": {
			                        "title": "Timing method",
			                        "type": "string",
			                        "enum": [
			                            "inview"
			                        ],
			                        "default": "inview"
			                    },
			                    "selector": {
			                        "title": "CSS selector",
			                        "type": "string",
			                        "minLength": 1
			                    },
			                    "offset": {
			                        "title": "Offset in pixels from the edge of the element.",
			                        "type": "number"
			                    }
			                },
			                "required": ["type", "selector"]
			            }, {
			                "type": "object",
			                "properties": {
			                    "type": {
			                        "title": "Timing method",
			                        "type": "string",
			                        "enum": [
			                            "media"
			                        ],
			                        "default": "media"
			                    },
			                    "media": {
			                        "title": "Media query",
			                        "type": "string",
			                        "minLength": 1
			                    }
			                },
			                "required": ["type", "media"]
			            }]
			        }]
	            }]
	        }
        },
        "required": ["families"],
        "additionalProperties": false
    },
    "uniqueItems": true
}
```
</details>

# Font Face Observer Configuration

Font Face Observer is supported by all browsers. The Font Face Observer configuration of the plugin is based on a [JSON schema](https://pagespeed.pro/schemas/fonts.json) which can be easily edited using the provided JSON editor in the plugin.

[[https://github.com/o10n-x/font-optimization/blob/master/docs/images/font-face-observer-json-editor.png|alt=Font Face Observer Configuration]]

The configuration is a array of JSON objects. Each object is a group of fonts to load via the Font Face Observer. The group can contain it's own before and onload actions, enable/disable settings and configure group specific load and render timing. 

The `families` property is an array of JSON objects for individual fonts to load. Each font object should contain at least the properties `family` (the name of the font family). The optional `options` property is an object that can contain the properties `weight`, `style` and `stretch`. These options are directly passed to the Font Face Observer.

The `beforeload` and `onload` properties are an object that can define actions before and after loading a font. The object supports two properties: `classList` and `method`. 

The `method` property enables to call a javascript function before or after a font is loaded. The method receives the font family name as it's argument.

The `classList` property is an object containing the properties `add` and `remove` which can be a string or an array of strings containing the class names to add or remove. An optional `target` property enables to define a element target on which to add or remove the class(es). Without an `target` property the classes are added to the `documentElement` (`<html>`).

#### Example Configuration

```json
[
  {
    "families": [
      {
        "family": "Roboto",
        "options": {
          "weight": 400,
          "style": "normal"
        }
      }
    ],
    "beforeload": {
      "classList": {
        "add": "roboto-loading"
      }
    },
    "onload": {
      "classList": {
        "remove": "roboto-loading"
      }
    },
    "load_position": "timing",
    "load_timing": {
    	"type": "media",
    	"media": "screen and (max-width: 700px)"
	}
  }
]
```

<details/>
  <summary>JSON schema for Font Face Observer config</summary>

```json
{
    "title": "Font Face Observer configuration",
    "type": "array",
    "items": {
        "title": "Font load config",
        "type": "object",
        "properties": {
            "families": {
                "title": "Font family names to load",
                "type": "array",
                "items": {
                    "oneOf": [{
                        "title": "Font family name",
                        "type": "string",
                        "minLength": 1
                    }, {
                        "title": "Font family with options",
                        "type": "object",
                        "properties": {
                            "family": {
                                "title": "Font family name",
                                "type": "string",
                                "minLength": 1
                            },
                            "options": {
                                "title": "Web Font Observer options",
                                "type": "object",
                                "properties": {
                                    "weight": {
							            "oneOf": [{
							                "type": "string",
							                "enum": ["normal", "bold", "bolder", "lighter", "initial", "inherit"]
							            }, {
							                "type": "number",
							                "enum": [100, 200, 300, 400, 500, 600, 700, 800, 900]
							            }]
							        },
                                    "style": {
							            "type": "string",
							            "enum": ["normal", "italic", "oblique", "initial", "inherit"]
							        },
                                    "stretch": {
							            "type": "string",
							            "enum": ["ultra-condensed", "extra-condensed", "condensed", "semi-condensed", "normal", "semi-expanded", "expanded", "extra-expanded", "ultra-expanded", "initial", "inherit"]
							        }
                                },
                                "additionalProperties": false
                            }
                        },
                        "required": ["family"],
                        "additionalProperties": false
                    }]
                },
                "uniqueItems": true
            },
            "beforeload": {
                "title": "Before font load actions",
                "type": "object",
                "properties": {
                    "classList": {
			            "title": "Classlist modifications",
			            "type": "object",
			            "properties": {
			                "add": {
			                    "title": "Class name(s) to add",
			                    "oneOf": [{
			                        "type": "string",
			                        "minLength": 1
			                    }, {
			                        "type": "array",
			                        "items": {
			                            "type": "string",
			                            "minLength": 1
			                        },
			                        "uniqueItems": true
			                    }]
			                },
			                "remove": {
			                    "title": "Class name(s) to remove",
			                    "oneOf": [{
			                        "type": "string",
			                        "minLength": 1
			                    }, {
			                        "type": "array",
			                        "items": {
			                            "type": "string",
			                            "minLength": 1
			                        },
			                        "uniqueItems": true
			                    }]
			                },
			                "target": {
			                    "title": "QuerySelector for class modification",
			                    "type": "string"
			                }
			            },
			            "additionalProperties": false
			        },
                    "method": {
                        "title": "Javascript method to call",
                        "type": "string"
                    }
                },
                "additionalProperties": false
            },
            "onload": {
                "title": "After font load actions",
                "type": "object",
                "properties": {
                    "classList": {
			            "title": "Classlist modifications",
			            "type": "object",
			            "properties": {
			                "add": {
			                    "title": "Class name(s) to add",
			                    "oneOf": [{
			                        "type": "string",
			                        "minLength": 1
			                    }, {
			                        "type": "array",
			                        "items": {
			                            "type": "string",
			                            "minLength": 1
			                        },
			                        "uniqueItems": true
			                    }]
			                },
			                "remove": {
			                    "title": "Class name(s) to remove",
			                    "oneOf": [{
			                        "type": "string",
			                        "minLength": 1
			                    }, {
			                        "type": "array",
			                        "items": {
			                            "type": "string",
			                            "minLength": 1
			                        },
			                        "uniqueItems": true
			                    }]
			                },
			                "target": {
			                    "title": "QuerySelector for class modification",
			                    "type": "string"
			                }
			            },
			            "additionalProperties": false
			        },
                    "method": {
                        "title": "Javascript method to call",
                        "type": "string"
                    }
                },
                "additionalProperties": false
            },
            "load_position": {
                "title": "Load position of web fonts",
                "type": "string",
                "enum": ["header", "timing"],
                "default": "header"
            },
            "load_timing": {
	            "title": "Timing configuration",
	            "oneOf": [{
	                "type": "object",
	                "properties": {
	                    "type": {
	                        "title": "Timing method",
	                        "type": "string",
	                        "enum": [
	                            "domReady"
	                        ],
	                        "default": "domReady"
	                    }
	                },
	                "required": ["type"]
	            }, {
	                "type": "object",
	                "properties": {
	                    "type": {
	                        "title": "Timing method",
	                        "type": "string",
	                        "enum": [
	                            "requestAnimationFrame"
	                        ],
	                        "default": "requestAnimationFrame"
	                    },
	                    "frame": {
	                        "title": "Frame number to start script execution.",
	                        "oneOf": [{
	                            "type": "string",
	                            "enum": [""]
	                        }, {
	                            "type": "number",
	                            "minimum": 1,
	                            "default": 1
	                        }]
	                    }
	                },
	                "required": ["type"]
	            }, {
	                "type": "object",
	                "properties": {
	                    "type": {
	                        "title": "Timing method",
	                        "type": "string",
	                        "enum": [
	                            "inview"
	                        ],
	                        "default": "inview"
	                    },
	                    "selector": {
	                        "title": "CSS selector",
	                        "type": "string",
	                        "minLength": 1
	                    },
	                    "offset": {
	                        "title": "Offset in pixels from the edge of the element.",
	                        "type": "number"
	                    }
	                },
	                "required": ["type", "selector"]
	            }, {
	                "type": "object",
	                "properties": {
	                    "type": {
	                        "title": "Timing method",
	                        "type": "string",
	                        "enum": [
	                            "media"
	                        ],
	                        "default": "media"
	                    },
	                    "media": {
	                        "title": "Media query",
	                        "type": "string",
	                        "minLength": 1
	                    }
	                },
	                "required": ["type", "media"]
	            }]
	        }
        },
        "required": ["families"],
        "additionalProperties": false
    }
```
</details>

# Google Web Font Loader Configuration

The Web Font Loader by Google has it's own methods for defining actions before and after a font is loaded, but the plugin adds to that the unique ability to time the Google Font Loader based on element scrolled into view or for example a Media Query so that fonts are only loaded when needed.

The plugin provides a javascript editor for the `WebFontConfig` variable. The variable is dynamicly processed using the `o10n.fonts` client. The client includes the latest version of Google's font loader integrated in the client for optimal performance.

[[https://github.com/o10n-x/font-optimization/blob/master/docs/images/google-font-loader-editor.png|alt=Google Font Loader Configuration]]

The plugin enables to remove existing Google Font Loader configuration, both links to `webfont.js` and WebFontConfig by replacing it with an empty IIFE.
