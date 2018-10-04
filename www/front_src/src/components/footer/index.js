import React, { Component } from "react";

class Footer extends Component {
  render() {
    return (
      <footer class="footer">
        <div class="footer-wrap">
          <div class="footer-wrap-left"></div>
          <div class="footer-wrap-middle">
            <ul class="list-unstyled footer-list">
              <li class="footer-list-item">
                <a href="https://documentation.centreon.com/" target="_blank" rel="noopener noreferrer">Documentation</a>
              </li>
              <li class="footer-list-item">
                <a href="https://support.centreon.com" target="_blank" rel="noopener noreferrer">Centreon Support</a>
              </li>
              <li class="footer-list-item">
                <a href="https://www.centreon.com" target="_blank" rel="noopener noreferrer">Centreon</a>
              </li>
              <li class="footer-list-item">
                <a href="https://github.com/centreon/centreon.git" target="_blank" rel="noopener noreferrer">Github Project</a>
              </li>
              <li class="footer-list-item">
                <a href="https://centreon.github.io" target="_blank" rel="noopener noreferrer">Slack</a>
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
