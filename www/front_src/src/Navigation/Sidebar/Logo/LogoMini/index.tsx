import { useAtomValue } from 'jotai/utils';
import { equals } from 'ramda';

import makeStyles from '@mui/styles/makeStyles';

import { ThemeMode, userAtom } from '@centreon/ui-context';

import miniLogoDark from './Centreon_Logo_Noir_RVB_C.svg';
import miniLogoLight from './Centreon_Logo_Blanc_C.svg';

interface Props {
  onClick?: () => void;
}
const useStyles = makeStyles((theme) => ({
  miniLogo: {
    height: theme.spacing(5),
    width: theme.spacing(3),
  },
}));

const MiniLogo = ({ onClick }: Props): JSX.Element => {
  const classes = useStyles();
  const { themeMode } = useAtomValue(userAtom);
  const miniLogo = equals(themeMode, ThemeMode.light)
    ? miniLogoDark
    : miniLogoLight;

  return (
    <div aria-hidden onClick={onClick}>
      <img alt="mini logo" className={classes.miniLogo} src={miniLogo} />
    </div>
  );
};

export default MiniLogo;
