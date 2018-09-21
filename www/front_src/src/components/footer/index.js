import React, { Component } from "react";
import { Link } from "react-router-dom";

class Footer extends Component {
  render = () => {
    return (
      <footer class="footer">
        <div class="footer-wrap">
          <div class="footer-wrap-left">
            <span>Generated in 0.392 seconds</span>
          </div>
          <div class="footer-wrap-middle">
            <ul class="list-unstyled footer-list">
              <li class="footer-list-item">
                <Link to="https://documentation.centreon.com/" target="_blank">Documentation</Link>
              </li>
              <li class="footer-list-item">
                <Link to="https://support.centreon.com" target="_blank">Centreon Support</Link>
              </li>
              <li class="footer-list-item">
                <Link to="https://www.centreon.com" target="_blank">Centreon</Link>
              </li>
              <li class="footer-list-item">
                <Link to="https://github.com/centreon/centreon.git" target="_blank">Github Project</Link>
              </li>
              <li class="footer-list-item">
                <Link to="https://centreon.github.io" target="_blank">Slack</Link>
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
