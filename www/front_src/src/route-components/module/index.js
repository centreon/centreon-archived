import React, { Component } from "react";
import Loader from "../../components/loader";
import axios from '../../axios';
import {DynamicComponentBundle} from '@centreon/react-components';

class ModuleRoute extends Component {

  state = {
    contentHeight: 0,
    loading: true,
    initialized:false,
    is_react:false,
    topology_name:'',
    topology_url:''
  }

  constructor(props) {
    super(props);
    this.mainContainer = null;
    this.resizeTimeout = null;
  }

  topologyApi = null;

  handleResize = () => {
    // wait size is the same during 200ms to handle it
    clearTimeout(this.resizeTimeout);

    if (this.mainContainer) {
      this.resizeTimeout = setTimeout(() => {
        const { clientHeight } = this.mainContainer;
        const { contentHeight } = this.state;
        if (clientHeight != contentHeight) {
          this.setState({
            loading: false,
            contentHeight: clientHeight - 30
          });
        }
      }, 200);
    }
  }

  handleHref = event => {
    let href = event.detail.href;
    window.history.pushState(null, null, href);
  }

  getQueryStringParams = (query,callback) => {
    let result = query
        ? (/^[?#]/.test(query) ? query.slice(1) : query)
            .split('&')
            .reduce((params, param) => {
                    let [key, value] = param.split('=');
                    params[key] = value ? decodeURIComponent(value.replace(/\+/g, ' ')) : '';
                    return params;
                }, {}
            )
        : {};
        callback(result);
  };

  componentDidMount() {
    const { history } = this.props,
    { search } = history.location;
    if(search.length > 0){
      this.getQueryStringParams(search,({p}) => {
        this.topologyApi = axios(`internal.php?object=centreon_topology&action=getTopologyByPage&topology_page=${p}`);
        this.topologyApi.get().then(({data})=> {
          const { is_react,topology_url, topology_name } = data;
          this.setState({
            is_react:(is_react == '1'),
            topology_url,
            topology_name,
            initialized:true,
            loading:false
          })
        })
      } )
      
    }else{
      this.setState({
        initialized:true,
        loading:false
      })
    }
    this.mainContainer = window.parent.document.getElementById('fullscreen-wrapper');
  

    // add a listener on global page size
    window.parent.addEventListener(
      "resize",
      this.handleResize
    );

    // add event listener to update page url
    window.addEventListener(
      "react.href.update",
      this.handleHref,
      false
    );
  };

  componentWillUnmount() {
    clearTimeout(this.resizeTimeout);
    window.parent.removeEventListener(
      "resize",
      this.handleResize
    );

    window.parent.removeEventListener(
      "react.href.update",
      this.handleHref
    );
  }

  render() {
    const { contentHeight, loading, topology_name, is_react, initialized, topology_url } = this.state;
    const { history } = this.props,
          { search, hash } = history.location;
    let params;
    if (window['fullscreenSearch']) {
      params = window['fullscreenSearch'] + window['fullscreenHash']
    } else {
      params = (search || '') + (hash || '');
    }
    return (
      <>
        {loading &&
          <span className="main-loader">
            <Loader />
          </span>
        }
        {
          initialized ? (
            <React.Fragment>
              {
                !is_react ? ( 
                <iframe
                  id="main-content"
                  title="Main Content"
                  frameBorder="0"
                  onLoad={this.handleResize}
                  scrolling="yes"
                  className={loading ? "hidden" : ""}
                  style={{ width: "100%", height: `${contentHeight}px` }}
                  src={`/_CENTREON_PATH_PLACEHOLDER_/main.get.php${params}`}
                />
              ) : 
                (
                <DynamicComponentBundle componentName={topology_name} topologyUrl={topology_url}/>
              )
              }
            </React.Fragment>
          ) : null
        }
        
      </>
    );
  }
}

export default ModuleRoute;
