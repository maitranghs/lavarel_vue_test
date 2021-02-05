import {mount} from 'vue-test-utils'
import expect from 'expect'
import Upload from '../../resources/js/components/UploadComponent.vue'

describe('Upload', () => {
  let component

  beforeEach(() => {
    component = mount(Upload)
  })

  test('contains Upload', () => {
    expect(component.html()).toContain('Upload File Form')
  })

})