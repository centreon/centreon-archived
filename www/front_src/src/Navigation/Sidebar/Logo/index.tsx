import { useAtomValue } from 'jotai/utils';
import { equals } from 'ramda';

import makeStyles from '@mui/styles/makeStyles';

import { ThemeMode, userAtom } from '@centreon/ui-context';

import logoDark from './Centreon_Logo_Noir_RVB.svg';
import logoLight from './Centreon_Logo_Blanc.svg';

interface Props {
  onClick?: () => void;
}
const useStyles = makeStyles((theme) => ({
  logo: {
    height: theme.spacing(5),
    width: theme.spacing(16.9),
  },
}));

const Logo = ({ onClick }: Props): JSX.Element => {
  const classes = useStyles();
  const { themeMode } = useAtomValue(userAtom);
  const logo = equals(themeMode, ThemeMode.light) ? logoDark : logoLight;

  return (
    <div aria-hidden onClick={onClick}>
      <img alt="logo" className={classes.logo} src={logo} />
    </div>
  );
};

export default Logo;
