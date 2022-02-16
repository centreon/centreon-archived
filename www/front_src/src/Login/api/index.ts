import axios from 'axios';

import { Login, Redirect } from '../models';

import { loginEndpoint } from './endpoint';

const postLogin =
  () =>
  (params: Login): Promise<Redirect> =>
    axios.post(loginEndpoint, params).then(({ data }) => data);

export default postLogin;
