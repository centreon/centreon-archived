import React, { Component } from "react";
import { Link } from "react-router-dom";

class Footer extends Component {
  render() {
    return (
      <footer class="footer">
        <div class="footer-wrap">
          <div class="footer-wrap-left">
            <span>Generated in 0.392 seconds</span>
          </div>
          <div class="footer-wrap-middle">
            <ul class="list-unstyled footer-list">
              <li class="footer-list-item">
                <a href="https://documentation.centreon.com/" target="_blank">Documentation</a>
              </li>
              <li class="footer-list-item">
                <a href="https://support.centreon.com" target="_blank">Centreon Support</a>
              </li>
              <li class="footer-list-item">
                <a href="https://www.centreon.com" target="_blank">Centreon</a>
              </li>
              <li class="footer-list-item">
                <a href="https://github.com/centreon/centreon.git" target="_blank">Github Project</a>
              </li>
              <li class="footer-list-item">
                <a href="https://centreon.github.io" target="_blank">Slack</a>
              </li>
            </ul>
          </div>
          <div class="footer-wrap-right">
            <span>Copyright &copy; 2005 - 2018</span>
          </div>
        </div>
      </footer>
    );
  };
}

export default Footer;
