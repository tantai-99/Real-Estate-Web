/**
 * Very simple basic rule set
 *
 * Allows
 *    <i>, <em>, <b>, <strong>, <p>, <div>, <a href="http://foo"></a>, <br>, <span>, <ol>, <ul>, <li>
 *
 * For a proper documentation of the format check advanced.js
 */
var wysihtml5ParserRules = {
  classes: {
  "tx-color1": 1,
  "tx-color2": 1,
  "tx-color3": 1,
  "tx-stress": 1,
  "tar": 1,
  "tac": 1,
  "tal": 1
  },
  tags: {
    strong: {},
    p:      {},
    span:   {},
    br:     {},
    a:      {
      check_attributes: {
        href:   "url", // important to avoid XSS
        target: "any"
      }
    }
  }
};